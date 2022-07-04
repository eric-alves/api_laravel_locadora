<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carro;
use App\Repositories\CarroRepository;

class CarroController extends Controller
{
    public function __construct(Carro $carro){
        $this->carro = $carro;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);

        if ($request->has("atributos_modelo")) {
            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        }else {
            $carroRepository->selectAtributosRegistrosRelacionados("modelo");
        }

        if ($request->has("filtro")) {
            $carroRepository->filtros($request->filtro);
        }

        if ($request->has("atributos")) {
            $carroRepository->selectAtributos($request->atributos);
        }

        return response()->json($carroRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->carro->rules());

        $carro = $this->carro->create($request->all());

        return response()->json($carro, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $carro = $this->carro->with("modelo")->find($id);
        if ($carro === null) {
            return response()->json(["error" => "Carro não existe!"], 404);
        }
        return response()->json($carro, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  @param  Illuminate\Http\Request  $request
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $carro = $this->carro->find($id);
        if ($carro === null) {
            return response()->json(["error" => "Carro não existe!"], 404);
        }

        $dynamicRules = array();

        if ($request->method() === 'PATCH') {
            foreach ($carro->rules() as $input => $rules) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRules[$input] = $rules;
                }
            }

            $request->validate($dynamicRules);
        } else {
            $request->validate($carro->rules());
        }

        $carro->fill($request->all());
        
        $carro->save();

        return response()->json($carro, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $carro = $this->carro->find($id);
        if ($carro === null) {
            return response()->json(["error" => "Carro não existe!"], 404);
        }

        $carro->delete();
        return ["msg" => "Carro removido com sucesso!"];
    }
}
