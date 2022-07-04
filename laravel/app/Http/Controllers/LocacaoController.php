<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Locacao;
use App\Repositories\LocacaoRepository;

class LocacaoController extends Controller
{
    public function __construct(Locacao $locacao){
        $this->locacao = $locacao;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $locacaoRepository = new LocacaoRepository($this->locacao);

        if ($request->has("atributos_carro")) {
            $atributos_carro = 'carro:id,'.$request->atributos_carro;
            $locacaoRepository->selectAtributosRegistrosRelacionados($atributos_carro);
        }else {
            $locacaoRepository->selectAtributosRegistrosRelacionados("carro");
        }

        if ($request->has("atributos_cliente")) {
            $atributos_cliente = 'cliente:id,'.$request->atributos_cliente;
            $locacaoRepository->selectAtributosRegistrosRelacionados($atributos_cliente);
        }else {
            $locacaoRepository->selectAtributosRegistrosRelacionados("cliente");
        }

        if ($request->has("filtro")) {
            $locacaoRepository->filtros($request->filtro);
        }

        if ($request->has("atributos")) {
            $locacaoRepository->selectAtributos($request->atributos);
        }

        return response()->json($locacaoRepository->getResultado(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->locacao->rules());

        $locacao = $this->locacao->create($request->all());

        return response()->json($locacao, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $locacao = $this->locacao->find($id);
        if ($locacao === null) {
            return response()->json(["error" => "Locação não existe!"], 404);
        }
        return response()->json($locacao, 200);
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
        $locacao = $this->locacao->find($id);
        if ($locacao === null) {
            return response()->json(["error" => "Locacao não existe!"], 404);
        }

        $dynamicRules = array();

        if ($request->method() === 'PATCH') {
            foreach ($locacao->rules() as $input => $rules) {
                if (array_key_exists($input, $request->all())) {
                    $dynamicRules[$input] = $rules;
                }
            }

            $request->validate($dynamicRules);
        } else {
            $request->validate($locacao->rules());
        }

        $locacao->fill($request->all());
        
        $locacao->save();

        return response()->json($locacao, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $locacao = $this->locacao->find($id);
        if ($locacao === null) {
            return response()->json(["error" => "Locacao não existe!"], 404);
        }

        $locacao->delete();
        return ["msg" => "Locacao removido com sucesso!"];
    }
}
