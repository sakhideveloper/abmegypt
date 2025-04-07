<?php

namespace App\Modules\Task\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Modules\Task\Requests\CreateTaskRequest;
use App\Modules\Task\Requests\UpdateTaskRequest;
use App\Modules\Task\Services\TaskService;
use App\Http\Controllers\ApiController;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends ApiController
{
    protected  $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->success($this->service->getAll($request), Response::HTTP_OK);
    }

    public function store(CreateTaskRequest $request)
    {
        return $this->success($this->service->store($request->input()), Response::HTTP_OK);
    }

    public function show(Task $task)
    {
        return $this->service->find($task->id);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        return $this->success($this->service->update($request->input(), $task->id), Response::HTTP_OK);
    }

    public function destroy(Task $task)
    {
        if($this->service->destroy($task->id))
            return $this->success(['message'=>'Task deleted successfully'],Response::HTTP_OK);
        else
            return $this->error(['message'=> 'Task not found'], Response::HTTP_NOT_FOUND);   

    }

    
}
