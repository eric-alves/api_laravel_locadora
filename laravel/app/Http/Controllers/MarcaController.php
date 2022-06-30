<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function __construct(Marca $marca){
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $response = Marca::all();
        // return $response;

        return $this->marca->all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->marca->roles(), $this->marca->feedback());

        $marca = $this->marca->create($request->all());

        return $marca;
    }

    /**
     * Display the specified resource.
     *
    //  * @param  \App\Models\Marca  $marca
     * @param Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(["error" => "Marca não existe!"], 404);
        }
        return $marca;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\Marca  $marca
     * @param Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $marca->update($request->all());
        // return $marca;

        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(["error" => "Marca não existe!"], 404);
        }

        $dynamicRoles = array();

        if ($request->method() === 'PATCH') {
            foreach ($marca->roles() as $input => $roles) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRoles[$input] = $roles;
                }
            }

            $request->validate($dynamicRoles, $marca->feedback());
        } else {
            $request->validate($marca->roles(), $marca->feedback());
        }

        $marca->update($request->all());
        return $marca;
    }

    /**
     * Remove the specified resource from storage.
     *
    //  * @param  \App\Models\Marca  $marca
     * @param Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $marca->delete();
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(["error" => "Marca não existe!"], 404);
        }
        $marca->delete();
        return ["msg" => "Marca removida com sucesso!"];
    }
}
