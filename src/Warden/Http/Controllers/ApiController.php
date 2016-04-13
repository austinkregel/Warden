<?php

namespace Kregel\Warden\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Closure;

class ApiController extends Controller
{
    /**
     * @param         $model_name
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function g($model_name, Request $request)
    {
        $this->checkParams(func_get_args()); // Filler bullshit.
        $code = $request->ajax() ? 202 : 200;
        $returnable = ['message' => 'Method success, but nothing was done.', 'code' => $code];

        return response()->json($returnable, $code);
    }

    /**
     * @param         $model_name
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllModels($model_name, Request $request)
    {
        return $this->getSomeModels($model_name, $request, 50000);
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
        $field_names = $this->getFields($model);
        $all = $model::paginate($paginate);
        $i = 0;
        foreach ($all as $model) {
            foreach ($field_names as $f) {
                if (!in_array($f, $model->getHidden())) {
                    $returnable[$model_name.'s'][$i] = $model->toArray();
                }
            }
            ++$i;
        }
        if (empty($returnable)) {
            $returnable = [];
        }
        $status = $request->ajax() ? 202 : 200;

        return response()->json($returnable, $status);
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
     * @param Model $model
     *
     * @return array
     */
    private function getFields(Model $model)
    {
        $field_names = !empty($model->getVisible()) ? $model->getVisible() : $model->getFillable();
        $dates = !empty($model->getDates()) ? $model->getDates() : [];

        return array_merge($field_names, $dates);
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
            if ($request->ajax()) {
                return response()->json(['message' => 'No resource found!', 'code' => 404], 404);
            }

            return response(redirect('404'), 301);
        }
        // Need a way to validate the input for the model. If we then can not find any
        // way to validate the inputs then we might have some un-wanted inputs from
        // some of the users. We probably won't need to worry about validations.
        $input = $this->clearInput([
                'uuid' => $this->generateUUID(),
            ] + $request->all());

        $model->fill($input);
        if (!empty($model->password)) {
            $model->password = bcrypt($model->password);
        }
        $inputs = $model->getFillable();
        $relations = config('kregel.warden.models.'.$model_name.'.relations');
        if (!empty($relations)) {
            foreach ($input as $k => $i) {
                if (in_array($k, $relations) || !(empty($relations[$k]))) { // Check if there is a relation
                    // if it's in_array, it's not a closure, just have to sync. Otherwise it's a closure
                    // And we will have to call the closure and pass through the need model to it.
                    $model->$k()->sync($i);
                    $update_event = config('kregel.warden.models.' . $model_name . '.relations.' . $k . '.new');
                    if ($update_event instanceof Closure) {
                        $update_event($model);
                    }
                }
            }
        }
        $saved = $model->save();
        if (!$saved) {
            return response()->json(['message' => 'Failed to created resource', 'code' => 422], 422);
        }
        $status = $request->ajax() ? 202 : 200;
        if ($request->ajax()) {
            return response()->json(['message' => 'Successfully created resource', 'code' => $status], $status);
        }
        if ($request->has('_redirect')) {
            // Remove the base part of the url, and just grab the tail end of the desired redirect, that way the
            // User can't be redirected away from your website.
            return $this->returnRedirect('Successfully created resource', $request);
        }

        return redirect()->back()->with(['message' => 'Successfully created resource']);
    }

    public function clearInput($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (!isset($value) || $value === '') {
                    unset($input[$key]);
                }
            }

            return $input;
        }

        return $input;
    }

    /**
     * @return string
     */
    public function generateUUID()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
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
        if (empty($model) && $request->ajax()) {
            return response()->json(['message' => 'No resource found!', 'code' => 404], 404);
        } else {
            if (empty($model)) {
                return $this->returnRedirect('No resource found!', $request);
            }
        }
        $input = $this->clearInput($request->all()); // Remove the empty values.
        $this->validatePut($input, $model, $model_name); // Remove any values that are the same

        if (collect($input)->isEmpty()) { // if the input is empty,
            return response()->json(['message' => 'Nothing to update for resource', 'code' => 205], 205);
        }

        $input = collect($model->toArray())->merge($input)->toArray();

        $model->fill($input);

        // Removed the file handling code, as put requested files come in via stdin.
        // Just use a post request unless you NEED the put request.

        $relations = config('kregel.warden.models.'.$model_name.'.relations');
        if (!empty($relations)) {
            foreach ($input as $k => $i) {

                if (in_array($k, $relations) || !(empty($relations[$k]))) { // Check if there is a relation
                    // if it's in_array, it's not a closure, just have to sync. Otherwise it's a closure
                    // And we will have to call the closure and pass through the need model to it.
                    $users = [];
                    foreach($model->$k as $user){
                        $users[] = $user->id;
                    }
                    if($users == $i) {
                        $model->$k()->sync($i);
                        $update_event = config('kregel.warden.models.' . $model_name . '.relations.' . $k . '.update');
                        if ($update_event instanceof Closure) {
                            $update_event($model);
                        }
                    }
                }
            }
        }

        $saved = $model->save();
        if (!$saved) {
            return response()->json(['message' => 'Failed to updated resource', 'code' => 422], 422);
        }
        if ($request->has('_redirect')) {
            // Remove the base part of the url, and just grab the tail end of the desired redirect, that way the
            // User can't be redirected away from your website.
            return $this->returnRedirect('Successfully updated resource', $request);
        }
        $status = $request->ajax() ? 202 : 200;

        return response()->json(['message' => 'Successfully updated resource', 'code' => $status], $status);
    }

    /**
     * This Checks for any values and the _token for csrf and removes it from any
     * blank values and it also removes the _token from the input. If there is
     * a password within the request it will compare it to the current hash.
     *
     * @param $input
     * @param $model
     */
    public function validatePut(&$input, $model, $model_name)
    {
        foreach ($input as $k => $v) {
            if (empty($v) || $k === '_token') {
                unset($input[$k]);
            }
            if (!empty($model->$k)) {
                if ($model->$k === $v) {
                    unset($input[$k]);
                }
            }
            if ($this->doesModelRelate($model, $k, $v)) {
                unset($input[$k]);
            }
            if (((stripos($k, 'password') !== false) || (stripos($k, 'passwd') !== false)) && !empty($model->$k)) {
                if (\Hash::check($v, $model->$k)) {
                    unset($input[$k]);
                } else {
                    $user_model = config('auth.model');
                    $user = new $user_model();
                    if (empty($user->hashable)) {
                        $input[$k] = bcrypt($v);
                    }
                }
            }
        }
    }

    private function doesModelRelate(Model $model, $relation, $objects)
    {
        $relations = config('kregel.warden.models.'.$relation.'.relations');
        if ($relations !== null && in_array($relation, $relations)) {
            if (is_array($objects)) {
                foreach ($objects as $k => $v) {
                    if ($model->$relation->contains($v)) {
                        return true;
                    }
                }
            } else {
                return (bool) $model->$relation->contains($objects);
            }
        }

        return false;
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
                    if($update_event instanceof Closure){
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
}
