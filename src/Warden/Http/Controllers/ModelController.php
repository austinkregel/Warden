<?php

namespace Kregel\Warden\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Input;
use Kregel\FormModel\FormModel;
use Route;
use Session;

class ModelController extends Controller
{
    public function __construct()
    {
        $this->middleware(config('kregel.warden.auth.middleware_name'));
    }

    /**
     * @param string $model_name A key in the warden.models configuration
     *
     * @return view
     */
    protected function getModelList($model_name)
    {
        /*
         * Here we need to make sure that we have all of the fields that we want to
         * have shown, if the visible field is not currently set we should grab
         * the fillable fields so we can populate the table on the view.
         */
        $model = $this->findModel($model_name, null);

        /*
         * We need to get the fields for the model, so use the model we newed up
         * above and grab the Visible field first, if it's not set then grab
         * the fillable fields.
         */
        $field_names = !empty($model->getVisible()) ?
            $model->getVisible() :
            $model->getFillable();
        // Paginate the results to plan for lots of items within the database.
        $desired_objects = $model::paginate(50);

        // Inject a quick form for deleting the objects.

        return view('warden::view-models')
            ->with('field_names', ($field_names))
            ->with('models', $desired_objects)
            ->with('model_name', $model_name);
    }

    /**
     * @param string $model_name Must be a valid class
     * @param int    $id         ID of the desired class
     *
     * @throws \Exception
     *
     * @return Model A model defined by ID
     */
    private function findModel($model_name, $id = null)
    {
        $model = config('kregel.warden.models.'.$model_name.'.model');
        if (empty($model)) {
            throw new \Exception('There is no model by the name {'.$model_name.'}');
        }
        if (empty($id) | !is_numeric($id)) {
            return new $model();
        }

        return $this->get('warden::api.get-model', [$model_name, $id]);
    }

    /**
     * @param string    $model_name A key in the warden.models configuration
     * @param int       $id         The id of the model
     * @param FormModel $form       An injected model
     *
     * @return mixed
     */
    protected function getModel($model_name, $id, FormModel $form)
    {
        /*
       * We need to grab the model from the config and select one entry for
       * that model from within the database.
       */
        $model = $this->findModel($model_name, $id)->getOriginalContent();
        /*
         * Here we generate the form to update the model using the kregel/formmodel
         * package
         */
        $form = $form->using(config('kregel.formmodel.using.framework'))
                            ->withModel($model)
                            ->submitTo(route('warden::api.update-model', [$model_name, $model->id]));
        $form_info = $form->form([
            'method'  => 'put',
            'enctype' => 'multipart/form-data',
        ]);

        return view('warden::view-model')
                ->with('form', $form_info)
                ->with('model_name', $model_name)
                ->with('vue_components', $form->vue_components)
                ->with('method', $form->options['method']);
    }

    /**
     * @param string    $model_name A key in the warden.models configuration
     * @param FormModel $form       An injected model
     *
     * @return mixed
     */
    protected function getNewModel($model_name, FormModel $form)
    {
        /*
         * We need to grab the model from the config and select one entry for
         * that model from within the database.
         */
        $model = $this->findModel($model_name);

        /*
         * Here we generate the form to update the model using the kregel/formmodel
         * package
         */
        $form_info = $form->using(config('kregel.formmodel.using.framework'))
            ->withModel($model)
            ->submitTo(route('warden::api.create-model', $model_name))
            ->form([
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ]);

        return view('warden::view-model')
                ->with('form', $form_info)
                ->with('model_name', $model_name);
    }

    /**
     * @depreciated Please use ajax to delete the element.
     *
     * @param string $model_name A key in the warden.models configuration
     * @param int    $id         The id of the model
     *
     * @return Route
     */
    protected function deleteModel($model_name, $id)
    {
        /*
         * Retrieve the desired model
         */
        $response = $this->delete('warden::api.delete-model', [$model_name, $id]);

        Session::flash('message', 'Hooray! It worked! The model was deleted!');

        return redirect(route('warden::models', $model_name));
    }

    /**
     * @param string $model_name A key in the warden.models configuration
     *
     * @return Route
     */
    protected function createModel($model_name)
    {
        $this->post('warden::api.create-model', [$model_name]);

        return redirect(route('warden::model', [$model_name, $model->id]));
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

    /**
     * @param $input_type
     * @param $input
     *
     * @return string
     */
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
