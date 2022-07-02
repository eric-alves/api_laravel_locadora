<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use App\Repositories\ModeloRepository;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function __construct(Modelo $modelo){
        $this->modelo = $modelo;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        if ($request->has("atributos_marca")) {
            $atributos_marca = 'marca:id,'.$request->atributos_marca;
            $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
        }else {
            $modeloRepository->selectAtributosRegistrosRelacionados("marca");
        }

        if ($request->has("filtro")) {
            $modeloRepository->filtros($request->filtro);
        }

        if ($request->has("atributos")) {
            $modeloRepository->selectAtributos($request->atributos);
        }

        return response()->json($modeloRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());

        $imagem = $request->file("imagem");
        $imagem_urn = $imagem->store("imagens/modelos", "public");
        $modelo = $this->modelo->create([
            "marca_id" => $request->marca_id, 
            "nome" => $request->nome, 
            "imagem" => $imagem_urn, 
            "numero_portas" => $request->numero_portas, 
            "lugares" => $request->lugares, 
            "air_bag" => $request->air_bag, 
            "abs" => $request->abs
        ]);

        return $modelo;
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = $this->modelo->with("marca")->find($id);
        if ($modelo === null) {
            return response()->json(["error" => "Modelo não encontrado"], 404);
        }

        return $modelo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(["error" => "Modelo não encontrado"], 404);
        }

        $dynamicRules = array();

        if ($request->method() === 'PATCH') {
            foreach ($modelo->rules() as $input => $rules) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRules[$input] = $rules;
                }
            }

            $request->validate($dynamicRules);
        } else {
            $request->validate($modelo->rules());
        }

        // Copia os campos exitentes em REQUEST para o OBJ MODELO
        $modelo->fill($request->all());

        if ($request->file("imagem")) {
            Storage::disk("public")->delete($modelo->imagem);
            $imagem = $request->file("imagem");
            $imagem_urn = $imagem->store("imagens/modelos", "public");
            $modelo->imagem = $imagem_urn;
        }

        $modelo->save();

        // $modelo->update([
        //     "marca_id" => $request->marca_id, 
        //     "nome" => $request->nome, 
        //     "imagem" => $imagem_urn, 
        //     "numero_portas" => $request->numero_portas, 
        //     "lugares" => $request->lugares, 
        //     "air_bag" => $request->air_bag, 
        //     "abs" => $request->abs
        // ]);
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(["error" => "Modelo não encontrado"], 404);
        }

        Storage::disk("public")->delete($modelo->imagem);

        return response()->json(["msg" => "Modelo removido com sucesso!"], 200);
    }
}
