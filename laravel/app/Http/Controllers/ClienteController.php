<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;

class ClienteController extends Controller
{
    public function __construct(Cliente $cliente){
        $this->cliente = $cliente;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $clienteRepository = new ClienteRepository($this->cliente);

        // if ($request->has("atributos_modelos")) {
        //     $atributos_modelos = 'modelos:marca_id,'.$request->atributos_modelos;
        //     $clienteRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        // }else {
        //     $clienteRepository->selectAtributosRegistrosRelacionados("modelos");
        // }

        if ($request->has("filtro")) {
            $clienteRepository->filtros($request->filtro);
        }

        if ($request->has("atributos")) {
            $clienteRepository->selectAtributos($request->atributos);
        }

        return response()->json($clienteRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->cliente->rules());
        $cliente = $this->cliente->create($request->all());

        return response()->json($cliente, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = $this->cliente->find($id);

        if ($cliente === null) {
            return response()->json(["error" => "Cliente não existe!"], 404);
        }

        return response()->json($cliente, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cliente = $this->cliente->find($id);
        $dynamicRules = array();

        if ($cliente === null) {
            return response()->json(["error" => "Cliente não encontrado!"], 404);
        }

        if ($request->method === "PATCH") {
            foreach ($cliente->rules() as $input => $rules) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRules[$input] = $rules;
                }
            }
            $request->validate($dynamicRules);
        } else {
            $request->validate($this->cliente->rules());
        }

        $cliente->fill($request->all());
        $cliente->save();

        return response()->json($cliente, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cliente = $this->cliente->find($id);

        if ($cliente === null) {
            return response()->json(["error" => "Cliente não encontrado!"], 404);
        }

        $cliente->delete();

        return response()->json(["msg" => "Cliente excluído com sucesso!"], 200);
    }
}
