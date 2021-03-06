<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use App\Repositories\MarcaRepository;
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
    public function index(Request $request)
    {
        $marcaRepository = new MarcaRepository($this->marca);

        if ($request->has("atributos_modelos")) {
            $atributos_modelos = 'modelos:marca_id,'.$request->atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        }else {
            $marcaRepository->selectAtributosRegistrosRelacionados("modelos");
        }

        if ($request->has("filtro")) {
            $marcaRepository->filtros($request->filtro);
        }

        if ($request->has("atributos")) {
            $marcaRepository->selectAtributos($$request->atributos);
        }

        return response()->json($marcaRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());
        
        $image = $request->file("imagem");
        $imagem_urn = $image->store("imagens/logos", "public");

        $marca = $this->marca->create([
            "nome" => $request->nome,
            "imagem" => $imagem_urn
        ]);

        return response()->json($marca, 201);
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
        $marca = $this->marca->with("modelos")->find($id);
        if ($marca === null) {
            return response()->json(["error" => "Marca n??o existe!"], 404);
        }
        return response()->json($marca, 200);
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
            return response()->json(["error" => "Marca n??o existe!"], 404);
        }

        $dynamicRules = array();

        if ($request->method() === 'PATCH') {
            foreach ($marca->rules() as $input => $rules) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRules[$input] = $rules;
                }
            }

            $request->validate($dynamicRules, $marca->feedback());
        } else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        // Copia os campos exitentes em REQUEST para o OBJ MARCA
        $marca->fill($request->all());

        if ($request->file("imagem")) {
            Storage::disk("public")->delete($marca->imagem);
            $imagem = $request->file("imagem");
            $imagem_urn = $imagem->store("imagens/logos", "public");
            $marca->imagem = $imagem_urn;
        }
        
        $marca->save();

        // $marca->update([
        //     "nome" => $request->nome,
        //     "imagem" => $imagem_urn
        // ]);
        return response()->json($marca, 200);
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
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(["error" => "Marca n??o existe!"], 404);
        }

        Storage::disk("public")->delete($marca->imagem);

        $marca->delete();
        return ["msg" => "Marca removida com sucesso!"];
    }
}
