<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post as RequestsPost;
use App\Models\Post;
// use Illuminate\Http\Request;

class PostController extends Controller
{

    // Creamos la Propiedad
    protected $post;
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Cuando no colocamos nada el asume que es 200
        return response()->json($this->post->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestsPost $request)
    {
        // Queremos crear todo lo que nos llega desde el request
        // $post = Post::create($request->all());
        $post = $this->post->create($request->all());
        // Retornamos una Respuesta en json con status http 201 (201: ok y creacion  de recursos)
        return response()->json($post, 201);
        // Refactorizamos (Refactorizar es mejorar el codigo sin modifcar su comportamiento)

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(RequestsPost $request, Post $post)
    {
        $post->update($request->all());

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        // no nesecitamos retornar datos pues estamos eliminando
        // Y retornamos un status 204 (sin contenido)
        return response()->json(null, 204);
    }
}
