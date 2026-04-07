<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'contenido' => ['required', 'string', 'max:5000'],
        ]);

        $task->comments()->create(['contenido' => $request->contenido]);

        return back();
    }

    public function destroy(TaskComment $comment)
    {
        $comment->delete();

        return back();
    }
}
