<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;

class PostControllerTest extends TestCase
{
    // Usamos esta Clase (RefreshDatabase) porque vamos a modificar datos dentro de mi base de datos
    // para que esto funcione debemos configurar los archivos que creamos:
    // -- Migracion posts
    // -- Como queremos salvar datos necesitamos configurar: El envio masivo (protected $fillable en el modelo Post)
    // -- Ahora Vamos al controlador y lo configuramos

    use RefreshDatabase;
    public function test_store()
    {

        // metodo que nos permite observar que esta pasando (que errores o a que se deben)
        // withoutExceptionHandling(): metodo manejador de errores
        // $this->withoutExceptionHandling();

        // ---------------------------------------------------------------------------------------//
        // Accedemos a la la ruta /api/posts (debemos crearla en route/api.php)
        // Enviamos el dato a traves de la ruta con metodo POST
        // Nota: dicha ruta nos lleva al controlador Api/PostController.php

        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [

            // Enviamos este dato
            'title' => 'El titulo Posts de Prueba'
        ]);
        // Cuando suceda el envio del dato y guardemos queremos que el sistema retorne
        // en una estructura JSON los datos id,title,create_at,updated_at

        // Esta Seria La Primera comprobacion
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            //segunda comprobacion: Afirmamos de nuevo que estamos teniendo correctamente lo que mandamos a guardar
            ->assertJson(['title' => 'El titulo Posts de Prueba'])
            // Tercera comprobacion: Confirmamos que estamos recibiendo el estatus http correcto
            ->assertStatus(201); // estatus http 201: la peticion se completado ok y se ha creado un recurso;

        // Comprobamos que en realidad contamos con esta informacion en la base de datos dentro de la table posts
        $this->assertDatabaseHas('posts', ['title' => 'El titulo Posts de Prueba']);
    }

    public function test_validate_title()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            // En esta caso no quiero enviar ningun titulo ( Validaremos campos vacios)
            // dicha validacion lo haremos desde un request
            'title' => ''
        ]);

        // Status http 422: significa que la solicitud esta bien echa pero fue imposible completarla
        // Porque estamos validando que no reciba titulos vacios
        $response->assertStatus(422) // status http 422
            // Comprobamos que estamos recibiendo un json aue incluye que el titulo no esta correcto
            ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = User::factory()->create();
        // debemos crear el factory
        $post = Post::factory()->create();

        // quiero acceder mediate get al post que se esta creando
        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id"); // como es una unica prueba se crea solo el post id = 1

        // Confirmamos que estamos obteniendo correctamente la esctructura siguiente
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])

            // que estamos obteniendo correctamente el titulo
            // comprobamos que el titulo de este post lo estamos accediendo
            // y que este coincida con el post que estoy creando
            ->assertJson(['title' => $post->title])

            ->assertStatus(200); // status http 200: OK

    }

    public function test_404_show()
    {
        $user = User::factory()->create();
        // quiero acceder mediate get al post que se esta creando
        // Sabemos que el post 1000 no existe
        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000')
            ->assertStatus(404); // status http 404:
    }

    public function test_update()
    {
        $user = User::factory()->create();
        // $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", [

            //Actualizamos este dato
            'title' => 'nuevo'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            //segunda comprobacion: Afirmamos de nuevo que estamos teniendo correctamente lo que mandamos a Actualizar
            ->assertJson(['title' => 'nuevo'])
            // Tercera comprobacion: Confirmamos que estamos recibiendo el estatus http correcto
            ->assertStatus(200); // estatus http 200: ok;

        // Comprobamos que en realidad contamos con esta informacion en la base de datos dentro de la table posts
        $this->assertDatabaseHas('posts', ['title' => 'nuevo']);
    }

    public function test_delete()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $response = $this->actingAs($user, 'api')->json('delete', "/api/posts/$post->id");
        $response->assertSee(null) // Garantizamos que no estamos recibiendo nada
            ->assertStatus(204); // Status http 204: Sin contenido (hace referencia a que no tenemos contenido)

        // Luego Verificamos que no exista dicho registro en la base de datos en la base de datos
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Post::factory(5)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/posts');

        $response->assertJsonStructure([
            'data' => [
                // * Que estoy obteniendo muchos datos
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200); // 200: ok;
    }

    /**
     * Proteccion de una Api con Login
     */

    // Metodo que prueba que sucede cuando llega un invitado
    public function test_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401); // Status http 401: No estamos autorizados al acceso
        $this->json('POST', '/api/posts')->assertStatus(401);
        $this->json('GET', '/api/posts/1000')->assertStatus(401);
        $this->json('PUT', '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }
}
