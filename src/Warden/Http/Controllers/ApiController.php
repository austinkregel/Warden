<?php
/**
 * Created by PhpStorm.
 * User: austin
 * Date: 10/29/15
 * Time: 1:55 PM
 */

namespace Kregel\Warden\Http\Controllers;


use Illuminate\Http\Request;

class ApiController extends Controller
{
//'warden::api.get-models'
/*
 * Try to make warden api first for useability.
 */
    /**
     * @param $model_name
     * @param Request $response
     * @throws \Exception
     */
    public function g($model_name, Request $response){
        $this->checkParams(func_get_args()); // Filler bullshit.
    }

    /**
     * @param $model_name
     * @param Request $request
     * @param int $paginate
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getSomeModels($model_name, Request $request, $paginate = 100){
        $this->checkParams(func_get_args());
        $model = $this->findModel($model_name);
        $field_names = $this->getFields($model);
        $all = $model::paginate($paginate);
        $i = 0;
        foreach($all as $model){
            foreach($field_names as $f)
                if(!in_array($f, $model->getHidden()))
                    $returnable[$i][$f] = $model->$f;
            $i++;
        }
        if($request->ajax())
            return response()->json($returnable, 202);
        return response()->json($returnable, 200);
    }

    /**
     * @param $model_name
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllModels($model_name, Request $request){
        return $this->getSomeModels($model_name, $request, 50000);
    }

    /**
     * @param $model_name
     * @param null $id
     * @return mixed
     */
    private function findModel($model_name, $id = null)
    {
        $model = config('kregel.warden.models.'.$model_name.'.model');
        if (empty($id) | !is_numeric($id))
            return new $model;
        return $model::find($id);
    }

    public function postModel($model_name, Request $request)
    {
        $this->checkParams(func_get_args());
        $model = $this->findModel($model_name);
        // Need a way to validate the input for the model. If we then can not find any
        // way to validate the inputs then we might have some un-wanted inputs from
        // some of the users. We probably won't need to worry about validations.
        $model->fill($request->all());
        $saved = $model->save();
        if(!$saved)
            return response()->json(['message'=>'Failed to created resource', 'code' => 422], 422);
        $status = $request->ajax()?202:200;
        return response()->json(['message'=>'Successfully created resource', 'code' => $request->ajax()?202:200], $status);
    }

    /**
     * @param $model
     * @return array
     */
    private function getFields($model){
        $field_names = !empty($model->getVisible()) ? $model->getVisible() : $model->getFillable();
        $dates = !empty($model->getDates())? $model->getDates() : [];

        return array_merge($field_names, $dates);
    }

}