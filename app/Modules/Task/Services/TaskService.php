<?php

namespace App\Modules\Task\Services;

use App\Models\Task;
use App\Modules\Task\Resources\TaskResource; // Ensure TaskResource is imported

class TaskService
{

    public function getAll($request): array
    {
        return TaskResource::collection(Task::with('user')->latest()->paginate($request->per_page ?? 10))->response()->getData(true);
    }

    public function getFieldsSearchable(): array
    {
        return [];
    }

    public function model(): string
    {
        return Task::class;
    }

    public function store($input): mixed
    {
        $input['user_id'] = auth()->user()->id; // Assuming you want to set the user_id to the authenticated user's ID
        $task = Task::create($input);
        return $this->find($task->id);
    }

    public function update($input, $id): mixed
    {
        $task = Task::findOrFail($id);
        $task->update($input);
        return $this->find($task->id);
    }

    public function destroy($id): bool
    {
        $task = Task::findOrFail($id);
        return $task->delete();
    }

    public function find($id): mixed
    {
        return new TaskResource(Task::with('user')->findOrFail($id));
    }


}