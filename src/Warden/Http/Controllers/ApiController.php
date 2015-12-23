<?php

namespace Kregel\Warden\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

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
		$code       = $request->ajax() ? 202 : 200;
		$returnable = [ 'message' => 'Method success, but nothing was done.', 'code' => $code ];

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
		$model       = $this->findModel($model_name);
		$field_names = $this->getFields($model);
		$all         = $model::paginate($paginate);
		$i           = 0;
		foreach ($all as $model) {
			foreach ($field_names as $f) {
				if ( ! in_array($f, $model->getHidden())) {
					$returnable[$model_name . 's'][$i] = $model->toArray();
				}
			}
			++$i;
		}
		if (empty( $returnable )) {
			$returnable = [ ];
		}
		$status = $request->ajax() ? 202 : 200;

		return response()->json($returnable, $status);
	}


	/**
	 * @param      $model_name
	 * @param null $id
	 *
	 * @return mixed
	 */
	public function findModel($model_name, $id = null)
	{
		$model = config('kregel.warden.models.' . $model_name . '.model');
		if (empty( $id ) | ! is_numeric($id)) {
			return new $model();
		}

		return $model::find($id);
	}


	/**
	 * @param $model
	 *
	 * @return array
	 */
	private function getFields($model)
	{
		$field_names = ! empty( $model->getVisible() ) ? $model->getVisible() : $model->getFillable();
		$dates       = ! empty( $model->getDates() ) ? $model->getDates() : [ ];

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
		if (empty( $model )) {
			return response()->json([ 'message' => 'No resource found!', 'code' => 404 ], 404);
		}
		// Need a way to validate the input for the model. If we then can not find any
		// way to validate the inputs then we might have some un-wanted inputs from
		// some of the users. We probably won't need to worry about validations.
		$input = array_merge([ 'uuid' => $this->generateUUID() ], $request->all());

		$model->fill($input);
		if ( ! empty( $model->password )) {
			$model->password = bcrypt($model->password);
		}
		$saved = $model->save();
		if ( ! $saved) {
			return response()->json([ 'message' => 'Failed to created resource', 'code' => 422 ], 422);
		}
		$status = $request->ajax() ? 202 : 200;

		return response()->json([ 'message' => 'Successfully created resource', 'code' => $status ], $status);
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
		if (empty( $model ) || empty( $request->ajax() )) {
			return response()->json([ 'message' => 'No resource found!', 'code' => 404 ], 404);
		}
		$input = $request->all();

		$this->validatePut($input, $model);
		if (empty( $input )) {
			return response()->json([ 'message' => 'Nothing to update for resource', 'code' => 205 ], 202);
		}

		if ($request->hasFile('path') && $request->file('path')->isValid()) {
			$file = $request->file('path');
			$path = base_path() . '/storage/pdfs/';
			$name = sha1_file($file->getClientOriginalName() . time(true)) . '.' . $file->getClientOriginalExtension();
			if ($file->move($path)) {
				$input['path'] = $path . $name;
			} else {
				return response()->json([ 'message' => $file->getErrorMessage(), 'code' => 422 ], 422);
			}
		}
		$model->fill($input);

		$saved = $model->save();
		if ( ! $saved) {
			return response()->json([ 'message' => 'Failed to updated resource', 'code' => 422 ], 422);
		}
		$status = $request->ajax() ? 202 : 200;

		return response()->json([ 'message' => 'Successfully updated resource', 'code' => $status ], $status);
	}


	/**
	 * @param         $model_name
	 * @param         $id
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function deleteModel($model_name, $id, Request $request)
	{
		$this->checkParams(func_get_args());

		$model = $this->findModel($model_name, $id);
		if (empty( $model )) {
			return response()->json([ 'message' => 'No resource found!', 'code' => 404 ], 404);
		}
		$model->delete();
		$status = $request->ajax() ? 202 : 200;

		return response()->json([ 'message' => 'Successfully deleted resource', 'code' => $status ], $status);
	}


	/**
	 * This Checks for any values and the _token for csrf and removes it from any
	 * blank values and it also removes the _token from the input. If there is
	 * a password within the request it will compare it to the current hash.
	 *
	 * @param $input
	 * @param $model
	 */
	public function validatePut(&$input, $model)
	{
		foreach ($input as $k => $v) {
			if (empty( $v ) || $k == '_token') {
				unset( $input[$k] );
			}
			if ( ! empty( $model->$k )) {
				if ($model->$k === $v) {
					unset( $input[$k] );
				}
			}
			if (( ( stripos($k, 'password') !== false ) || ( stripos($k,
							'passwd') !== false ) ) && ! empty( $model->$k )
			) {
				if (\Hash::check($v, $model->$k)) {
					unset( $input[$k] );
				} else {
					$input[$k] = bcrypt($v);
				}
			}
		}
	}


	/**
	 * @return string
	 */
	public function generateUUID()
	{
		$data    = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}
