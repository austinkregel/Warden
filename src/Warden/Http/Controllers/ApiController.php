<?php

namespace Kregel\Warden\Http\Controllers;

use Carbon\Carbon;
use Closure;
use FormModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Kregel\Warden\Warden;

class ApiController extends Controller
{
    protected $model_name;

    protected $model;

    /**
     * @param         $model_name
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllModels($model_name, Request $request)
    {
        $this->model_name = substr($model_name, 0, -1);

        return $this->getSomeModels($request, $request, 500);
    }

    /**
     * @param         $model_name
     * @param Request $request
     * @param int     $paginate
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSomeModels($model_name, Request $request, $paginate = 100)
    {
        $this->checkParams(func_get_args());
        $model = $this->findModel($model_name);
        $status = $request->ajax() ? 202 : 200;

        return response()->json($model->paginate($paginate), $status);
    }

    /**
     * @param      $model_name
     * @param null $id
     *
     * @return Model
     */
    public function findModel($model_name, $id = null)
    {
        $model = config('kregel.warden.models.'.$model_name.'.model');
        if (empty($id) | !is_numeric($id)) {
            return new $model();
        }

        return $model::find($id);
    }

    /**
     * @param         $model_name
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postModel($model_name, Request $request)
    {
        $this->checkParams(func_get_args());
        $model = $this->findModel($model_name);
        if (empty($model)) {
            return $this->emptyModel($request);
        }
        // Need a way to validate the input for the model. If we then can not find any
        // way to validate the inputs then we might have some un-wanted inputs from
        // some of the users. We probably won't need to worry about validations.
        $input = Warden::clearInput([
                'uuid' => Warden::generateUUID(),
            ] + $request->all());

        $model->fill($input);
        if (!empty($model->password)) {
            $model->password = bcrypt($model->password);
        }

        $this->uploadFileTest($model, $request);

        // Update a relationship.
        foreach ($input as $k => $i) {
            // If there are no relations for this point just break.
            if (empty($relations = FormModel::using('plain')->getRelationalDataAndModels($model, $k))) {
                break;
            }

            if (in_array($k, $relations) || !(empty($relations[$k]))) { // Check if there is a relation
                // if it's in_array, it's not a closure, just have to sync. Otherwise it's a closure
                // And we will have to call the closure and pass through the need model to it.
                $model->$k()->sync($i);
                $update_event = config('kregel.warden.models.'.$model_name.'.relations.'.$k.'.new');
                if ($update_event instanceof Closure) {
                    $update_event($model);
                }
            }
        }

        return $this->modelHasBeenSaved($model->save(), 'created', $request);
    }

    /**
     * This method handles how submitted quests are handle, the main related methods for it are
     * POST and.
     *
     * @param $saved
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function modelHasBeenSaved($saved, $type, $request)
    {
        if (!$saved) {
            return response()->json(['message' => 'Failed to '.$type.' resource', 'code' => 422], 422);
        }
        $status = $request->ajax() ? 202 : 200;
        if ($request->ajax()) {
            return response()->json(['message' => 'Successfully '.$type.' resource', 'code' => $status], $status);
        }
        if ($request->has('_redirect')) {
            // Remove the base part of the url, and just grab the tail end of the desired redirect, that way the
            // User can't be redirected away from your website.
            return $this->returnRedirect('Successfully '.$type.' resource', $request);
        }

        return redirect()->back()->with(['message' => 'Successfully '.$type.' resource']);
    }

    private function returnRedirect($msg, Request $request)
    {
        // Remove the base part of the url, and just grab the tail end of the desired redirect, that way the
        // User can't be redirected away from your website.
        $tmp = explode('/', preg_replace('/^(http|https):\/\//', '', $request->get('_redirect')));
        array_shift($tmp);

        return redirect()->to(implode('/', $tmp))->with(['message' => $msg]);
    }

    /**
     * @param         $model_name
     * @param         $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function putModel($model_name, $id, Request $request)
    {
        $this->checkParams(func_get_args());
        $model = $this->findModel($model_name, $id);
        if (empty($model)) {
            return $this->emptyModel($request);
        }
        $input = collect(Warden::clearInput($request->all())); // Remove the empty values.

        $this->validatePut($input, $model, $model_name); // Remove any values that are the same

        if ($input->isEmpty()) { // if the input is empty,
            return response()->json(['message' => 'Nothing to update for resource', 'code' => 205], 205);
        }

        /*
         * Here we need to calculate the actual values of our model. Since we're
         * just updating what we can then we only need the fillable from the model
         *
         * If the model has the Wardenable Trait then it skews the results with collections
         * and it also removes the _id's from the model and trims the value.
         *
         * If it has the Wardenable Trait, calculate the real values by flatMaping the values
         * of the getFillable array to the model it's self.
         */
        if (method_exists($model, 'getWarden')) {
            $model_array = collect($model->getFillable())->flatMap(function ($value) use ($model) {
                // If the value is an instance of carbon it will throw an 'Array to String' exception.
                if ($model->$value instanceof Carbon) {
                    // Convert the string to
                    return [$value => $model->$value->__toString()];
                }

                return [$value => $model->$value];
            });
        } else {
            // Since the model doesn't have getWarden then it doesn't have warden able.
            $model_array = collect($model->toArray());
        }
        // Since wardenable messes with our normal method of just collect($model)
        // So we have to map the fillable to a new model
        $relationships = $input->filter(function ($value) {
            return is_array($value);
        });
        // Assume that anything not in an array isn't a relationship.
        $not_relationships = $input->filter(function ($value) {
            return !is_array($value);
        });
        // Before filling the model we need to convert all date time stamps into normal timestamps
        $dates = collect($not_relationships)->filter(function ($val, $key) use ($model) {
            return in_array($key, $model->getDates());
        })->flatMap(function ($value, $key) use ($model) {
            if (count(explode(' ', $value)) > 1) {
                return [$key => Carbon::createFromFormat('Y-m-d H:i:s', $value)->__toString()];
            }
            $value = $value === '-0001-11-30' ? '0000-00-00' : $value;

            return [$key => Carbon::createFromFormat('Y-m-d', $value)->__toString()];
        });
//        dd($dates, $not_relationships, $relationships);
        // Fill the differences between the current values and the new input.
        $model->fill($not_relationships->merge($dates)->toArray());

        // Make sure the array
        if (!$relationships->isEmpty()) {
            foreach ($relationships as $many_relation => $values) {
                if ($model->$many_relation() instanceof BelongsToMany) {
                    $model->$many_relation()->sync($values, false);
                }
            }
        }
        $saved = $model->save();

        return $this->modelHasBeenSaved($saved, 'updated', $request);
    }

    /**
     * This Checks for any values and the _token for csrf and removes it from any
     * blank values and it also removes the _token from the input. If there is
     * a password within the request it will compare it to the current hash.
     *
     * @param $input
     * @param $model
     *
     * @return Collection
     */
    public function validatePut($input, $model, $model_name)
    {
        return collect($input)->filter(function ($value, $key) use ($model, $input) {
            // Remove _token
            if (!isset($value) || $key === '_token') {
                return false;
            }

            //Remove values that are the same.
            if (isset($model->$key)) {
                if ($model->$key === $value) {
                    return false;
                }
            }
            // Is there a relation? If there is, unset it.
            if ($this->doesModelRelate($model, $key, $value)) {
                return false;
            }

            // If there is a password field,
            if (((stripos($key, 'password') !== false) || (stripos($key, 'passwd') !== false)) && !empty($model->$key)) {
                if (\Hash::check($value, $model->$key)) {
                    return false;
                } else {
                    $user_model = config('kregel.warden.models.user.model');
                    $user = new $user_model();
                    if (empty($user->hashable)) {
                        $input[$key] = bcrypt($value);

                        return true;
                    }
                }
            }


            return true;
        });
    }

    /**
     * @param         $model_name
     * @param         $id
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteModel($model_name, $id, Request $request)
    {
        $this->checkParams(func_get_args());
        $status = $request->ajax() ? 202 : 200;

        $model = $this->findModel($model_name, $id);
        if (empty($model->id)) {
            if ($model = $model::withTrashed()->whereId($id)->first()) {
                $relations = config('kregel.warden.models.'.$model_name.'.relations');
                foreach ($relations as $rel) {
                    $model->$rel()->forceDelete();

                    $update_event = config('kregel.warden.models.'.$model_name.'.relations.'.$rel.'.delete');
                    if ($update_event instanceof Closure) {
                        $update_event($model);
                    }
                }
                $model->forceDelete();

                return response()->json(['message' => 'Resource deleted from the system.', 'code' => $status], $status);
            }

            return response()->json(['message' => 'No resource found!', 'code' => 404], 404);
        }
        $model->delete();
        if ($request->has('_redirect')) {
            // Remove the base part of the url, and just grab the tail end of the desired redirect, that way the
            // User can't be redirected away from your website.
            return $this->returnRedirect('Successfully updated resource', $request);
        }

        return response()->json(['message' => 'Successfully deleted resource', 'code' => $status], $status);
    }

    private function doesModelRelate(Model $model, $relation, $objects)
    {
        return FormModel::using('plain')->getRelationalDataAndModels($model, $relation) !== null;
    }

    public function displayMediaPage($model_name, $uuid) {
        $model = $this->findModel($model_name, $uuid);
        if (empty($model)) {
            return $this->emptyModel(request());
        }
        $filled = '';
        $data = (collect($model->getFillable())->filter(function($fillable) {
            return stripos($fillable, 'path') !== false;
        })->map(function($fill) use ($model, $uuid,&$filled) {
            $tmp = explode('.', $uuid)[0];
            return $model->where($filled = $fill, 'like', '%' . $tmp. '%')->first();
        })->filter(function ($val) {
            return !empty($val);
        }))->first();


        return response()->make(file_get_contents(storage_path('app/uploads/'.$data->$filled)))
            ->header('Content-type',mime_content_type(storage_path('app/uploads/'.$data->$filled)))
            ->header('Content-length', filesize(storage_path('app/uploads/'.$data->$filled)));
    }

    /**
     * Upload the file
     * @param Model $model
     * @param Request $request
     * @return void
     */
    private function uploadFileTest($model, Request $request) {
        // Filter through all the uploaded files, only grabbing the files in our
        // Fillable, (we don't want any extra things)
        $valid_files = collect($request->allFiles())->filter(function($file, $key) use ($model) {
            return in_array($key, $model->getFillable());
        });

        //  For each file process the upload.
        // Of course, if the collection of valid_files is empty, nothing will happen.
        $valid_files->each(function(\Illuminate\Http\UploadedFile $file, $key) use ($model) {
            $ext = $file->guessExtension();
            $name = Warden::generateUUID().'.'.$ext;
            $fs = new Filesystem();
            $fs->makeDirectory($file_path = storage_path('app/uploads'), 0755, true, true);

            $model->$key = $name;

            $file->move($file_path, $name);
        });
    }
}
