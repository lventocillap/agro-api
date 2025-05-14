<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Question\ValidateQuestion;


class QuestionController extends Controller
{
    use ValidateQuestion;
    
    /**
     * @OA\Get(
     *     path="/api/questions",
     *     summary="Obtener las Questions",
     *     tags={"Questions"},
     *     @OA\Response(
     *         response=200,
     *         description="Questions obtenida exitosamente",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Question not exists"
     *     )
     * )
     */
    public function getAll(Request $request) {

        $limit = $request->query('limit', 10);

        $customers = Question::paginate($limit);

        return response()->json([
            'data'         => $customers->items(),
            'current_page' => $customers->currentPage(),
            'total'        => $customers->total(),
            'last_page'    => $customers->lastPage(),
            'next_page'    => $customers->nextPageUrl(),
            'prev_page'    => $customers->previousPageUrl()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/questions",
     *     summary="Crear una nueva pregunta",
     *     tags={"Questions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question", "answer"},
     *             @OA\Property(property="question", type="string"),
     *             @OA\Property(property="answer", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pregunta creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request) {

        $this->validateQuestionRequest($request);

        $question = Question::create([
            'question' => $request->question,
            'answer' => $request->answer
        ]);

        
        return new JsonResponse(['message' => 'Creado Correctamente'],201);
    }

    /**
     * @OA\Get(
     *     path="/api/questions/{id}",
     *     summary="Obtener una pregunta por ID",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pregunta encontrada",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no encontrada"
     *     )
     * )
     */
    public function getById($id) {
        
        $question = Question::find($id);

        if (!$question) {
            return new JsonResponse(['message' => 'Question not Exists']);
        }

        return new JsonResponse($question, 200);
    }
    
    /**
     * @OA\Put(
     *     path="/api/questions/{id}",
     *     summary="Actualizar una pregunta existente",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question", "answer"},
     *             @OA\Property(property="question", type="string"),
     *             @OA\Property(property="answer", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pregunta actualizada",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no encontrada"
     *     )
     * )
     */
    public function update($id, Request $request) {
        
        $this->validateQuestionRequest($request);

        $question = Question::where('id', $id)->first();

        if ($question) {
            $question->update([
                'question' => $request->question,
                'answer'=> $request->answer
            ]);

            return new JsonResponse(['message' => 'Actualizado']);
        }

        return new JsonResponse(['message' => 'Question not Exists'],404);
    }

    /**
     * @OA\Delete(
     *     path="/api/questions/{id}",
     *     summary="Eliminar una pregunta",
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la pregunta",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pregunta eliminada",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pregunta no encontrada"
     *     )
     * )
     */
    public function delete($id) {
        
        $question = Question::where('id', $id)->first();

        if ($question) {
            $question->delete();

            return new JsonResponse(['message' => 'Eliminado']);
        }

        return new JsonResponse(['message' => 'Question Not Exists']);
    }
}
