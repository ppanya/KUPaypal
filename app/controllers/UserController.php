<?php

class UserController extends \BaseController {


	//public $restful = true;
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
		try{
	        $response = [
	            'users' => []
	        ];
	        $statusCode = 200;
	        $users = User::all();
	 
	        foreach($users as $user){
	 
	            $response['users'][] = [
	                'id' => $user->id,
	                'first_name' => $user->first_name,
	                'last_name' => $user->last_name,
	                'date_of_birth' => $user->date_of_birth,
	                'phone' => $user->phone,
	                'address' => $user->address
	            ];
	        }
	 
	 
	    }catch (Exception $e){
	        $statusCode = 404;
	    }finally{
	        return Response::json($response, $statusCode);
	    }
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('user.create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validator = Validator::make(Input::all(),
			array(
				'email' 				=> 'required|max:50|email|unique:users',
				'password'  			=> 'required|min:8',
				'password_confirmation' => 'required|same:password',
				'first_name'			=> 'required',
				'last_name'				=> 'required',
				'address'				=> 'required',
				'phone'					=> 'required'
			)
		);

		if($validator->fails()){
			return Redirect::route('users.create')
				   ->withErrors($validator)
				   ->withInput();
		} else {

			$dob = new DateTime(Input::get('year'). '-' .Input::get('month') . '-' . Input::get('day'));

			$date = new DateTime();

			$id = DB::table('users')->insertGetId(
				array(
					'first_name'=> Input::get('first_name'),
					'last_name'	=> Input::get('last_name'),
					'address'	=> Input::get('address'),
					'phone'		=> Input::get('phone'),
					'date_of_birth' => $dob->format('Y-m-d'),
					'email' 	=> Input::get('email'),
					'password'	=> Hash::make(Input::get('password')),
					'created_at' 	=> $date,
					'updated_at' 	=> $date
				)
			);

			DB::table('wallets')->insert(
				array(
					'owner_id' => $id,
					'balance'  => 0,
					'created_at' 	=> $date,
					'updated_at' 	=> $date
				)
			);

			return Redirect::route('users.index');
		}
		
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
		try{
 
	        $response = [
	            'user' => []
	        ];
	        $statusCode = 200;
	        $user = User::find($id);
	 
	            $response['user'][] = [
	                'id' => $user->id,
	                'first_name' => $user->first_name,
	                'last_name' => $user->last_name,
	                'date_of_birth' => $user->date_of_birth,
	                'phone' => $user->phone,
	                'address' => $user->address
	            ];
	 
	 
	    }catch (Exception $e){
	        $statusCode = 404;
	    }finally{
	        return Response::json($response, $statusCode);
	    }

	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		return View::make('user.profile' , array('id' => $id));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$user = User::find($id);

		$dob = new DateTime(Input::get('year'). '-' .Input::get('month') . '-' . Input::get('day'));

		$validator = Validator::make(Input::all(),
			array(
				'first_name'	=> 'required',
				'last_name'		=> 'required',
				'address'		=> 'required',
				'phone'			=> 'required'
			)
		);

		if($validator->fails()){
			return Redirect::route('users.edit' , array('id' => $id ))
				   ->withErrors($validator)
				   ->withInput();
		} else {

			$user->first_name = Input::get('first_name');
			$user->last_name = Input::get('last_name');
			$user->address  = Input::get('address');
			$user->phone  = Input::get('phone');
			$user->date_of_birth = $dob->format('Y-m-d');
			$user->save();

			return Redirect::route('users.index');

		}


	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function getSignIn() {
		return View::make('user.signin');
	}

	public function postSignIn() {

		$validator = Validator::make(Input::all(),
			array(
				'email' 	=> 'required|email|exists:users',
				'password'  => 'required|min:8'
			)
		);

		if($validator->fails()){
			return Redirect::route('user-sign-in')
				   ->withErrors($validator)
				   ->withInput(Input::except('password'));
		} else {

			$credentials = array(
				'email'		=> Input::get('email'),
				'password'	=> Input::get('password')
			);

			$auth = Auth::attempt($credentials , true);

			if($auth) {
				//Redirect to the intended page

				Session::put('user' , Auth::user() );

				return Redirect::intended('/');
			} else {
				return Redirect::route('user-sign-in');
			}
		}
	}


}
