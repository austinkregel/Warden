<?php

namespace Kregel\Warden\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Model;
use Session;
use FormModel;
use Input;

class ModelController extends BaseController
{
    public function __construct()
    {
        $this->middleware(config('kregel.warden.auth.middleware_name'));
    }
  /**
   * @param    String $model_name A key in the warden.models configuration
   * @return view
   */
  protected function getModelList($model_name)
  {
      /**
     * Here we need to make sure that we have all of the fields that we want to 
     * have shown, if the visible field is not currently set we should grab 
     * the fillable fields so we can populate the table on the view.
     */
    $model = $this->findModel($model_name, null);
    
    /**
     * We need to get the fields for the model, so use the model we newed up 
     * above and grab the Visible field first, if it's not set then grab
     * the fillable fields.
     */
    $field_names = !empty($model->getVisible()) ?
                          $model->getVisible() :
                          $model->getFillable();
    // Paginate the results to plan for lots of items within the database.
    $desired_objects = $model::paginate(50);
    return view('warden::view-models')
            ->with('field_names', ($field_names))
            ->with('models', $desired_objects)
            ->with('model_name', $model_name);
  }
  /**
   * @param    String    $model_name  A key in the warden.models configuration
   * @param    int       $id          The id of the model
   * @param    FormModel $form        An injected model
   * @return mixed
   */
  protected function getModel($model_name, $id = null, FormModel $form)
  {
      /**
     * We need to grab the model from the config and select one entry for
     * that model from within the database.
     */
    $model = $this->findModel($model_name, $id);
      
    /**
     * Here we need to make sure that we have all of the fields that we want to 
     * have shown, if the visible field is not currently set we should grab 
     * the fillable fields so we can populate the table on the view.
     */
    $field_names = !empty($model->getVisible()) ?
                          $model->getVisible() :
                          $model->getFillable();
    /**
     * Here we generate the form to update the model using the kregel/formmodel
     * package
     */
    $form_info = $form->modelForm($model, $field_names, route('warden::update-model', [$model_name, $model->id]), [/*This is for relations, TODO...*/], 'PUT');
    
      return view('warden::view-model')
            ->with('form', $form_info)
            ->with('model_name', $model_name);
      ;
  }
  
  /**
   * @param    String    $model_name  A key in the warden.models configuration
   * @param    int       $id          The id of the model
   * @param    FormModel $form        An injected model
   * @return mixed
   */
  protected function getNewModel($model_name, FormModel $form)
  {
      /**
     * We need to grab the model from the config and select one entry for
     * that model from within the database.
     */
    $model = $this->findModel($model_name);
      
    /**
     * Here we need to make sure that we have all of the fields that we want to 
     * have shown, if the visible field is not currently set we should grab 
     * the fillable fields so we can populate the table on the view.
     */
    $field_names = !empty($model->getVisible()) ?
                          $model->getVisible() :
                          $model->getFillable();
    /**
     * Here we generate the form to update the model using the kregel/formmodel
     * package
     */
    $form_info = $form->modelForm($model, $field_names, route('warden::create-model', [$model_name]), [/*This is for relations, TODO...*/], 'POST');
    
      return view('warden::view-model')
              ->with('form', $form_info)
              ->with('model_name', $model_name);
  }
  
  /**
   * @param    String    $model_name  A key in the warden.models configuration
   * @param    int       $id          The id of the model
   * @return Route
   */
  protected function deleteModel($model_name, $id)
  {
      /**
       * Retrieve the desired model
       */
      $model = $this->findModel($model_name, $id);
      /**
       * If the model doesn't exists redirect and throw an error.
       */
      if (empty($model)) {
          return redirect(route('warden::models', $model_name))->withErrors(['Sorry, but it looks like this model doesn\'t exist!']);
      }
      
      /**
       * Delete the model
       */
      $model->delete();
    
      Session::flash('message', 'Horray! It worked! The model was deleted!');
      return redirect(route('warden::models', $model_name));
  }
  
  /**
   * @param  String    $model_name  A key in the warden.models configuration
   * @param  int       $id          The id of the model
   * @return Route
   */
  protected function updateModel($model_name, $id)
  {
      /**
     * We need to grab the model from the config and select one entry for
     * that model from within the database.
     */
    
    $model = $this->findModel($model_name, $id);
    
    
      $this->sortInputAndApply($model, true);
    
      $model->save();
    
      return redirect(route('warden::model', [$model_name, $id]));
  }
  /**
   * @param  String    $model_name  A key in the warden.models configuration
   * @return Route
   */
  protected function createModel($model_name)
  {
      $model = $this->findModel($model_name);
    
      $this->sortInputAndApply($model);
      
      $model->save();
      
      return redirect(route('warden::model', [$model_name, $model->id]));
  }

  
  /**
   * @param  String $model_class Must be a valid class
   * @param  int    $id          ID of the desired class
   * @return Model A model defined by ID
   */
  private function findModel($model_name, $id = null)
  {
      $model = config('kregel.warden.models.'.$model_name);
      if (empty($model)) {
          throw new \Exception('There is no model by the name {'. $model_name.'}');
      }
      if (empty($id) | !is_numeric($id)) {
          return new $model();
      }
      return $model::find($id);
  }
  
  /**
   * @param Model $model This should be an eloquent based thing...
   * 
   * @return Model A newed up model
   */
  private function sortInputAndApply(Model &$model, $update = false)
  {
      // Loop through all input
    if ($update) {
        foreach (Input::all() as $key => $value) {
            // Check to see if there is any available value for the desired model.
            if (!empty($model->$key)) {
                // If the value is not equal to the desired value
              if ($model->$key !== $value) {
                  // Remove any extra spaces
                $model->$key = $this->manageInput($key, $value);
              }
            } elseif (Input::has('_relations')) {
                // Explode any potential relations.
                $relations = explode(',', Input::get('_relations'));
                foreach ($relations as $relation) {
                    // If the relation has the key, update the value.
                    if (!empty($model->$relation->$key)) {
                        if ($model->$relations->$key !== $value) {
                            $model->$relations->$key = $this->manageInput($key, $value);
                        }
                    }
                }
            }
        }
    } else {
        foreach ($model->getFillable() as $key) {
            foreach (Input::all() as $field => $value) {
                // If the fillable key doesn't match the input
                // field keep going,Otherwise, set the values.
                if ($key === $field) {
                    $model->$key = $this->manageInput($key, $value);
                }
            }
        }
    }
    // Unconventional return?
    return $model;
  }
  
    private function manageInput($input_type, $input)
    {
        switch (true) {
      case stripos($input_type, 'password') !== false:
        // Encrypt the password fields
        // The password field will not be shortend due to poor naming of
        // Other potential naming conflicts. ie. using pass will effect something 'compass'
        return bcrypt($input);
      // Don't know what other input will need pre-processing...
      default:
        // Remove any extra starting or trailing spaces.
        return trim($input);
    }
    }
}
