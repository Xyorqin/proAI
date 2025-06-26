<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Section\StoreRequest;
use App\Http\Requests\Admin\Section\UpdateRequest;
use App\Services\Admin\SectionService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    protected $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    public function index()
    {
        $sections = $this->sectionService->all();
        return response()->successJson(['data' => $sections]);
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $section = $this->sectionService->create($validated);

        return response()->successJson(['data' => $section, 'message' => 'Section created successfully.'], 201);
    }

    public function show($id)
    {
        $section = $this->sectionService->find($id);

        if (!$section) {
            return response()->errorJson(['message' => 'Section not found.'], 404);
        }

        return response()->successJson(['data' => $section]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $validated = $request->validated();

        $section = $this->sectionService->update($id, $validated);

        if (!$section) {
            return response()->errorJson(['message' => 'Section not found.'], 404);
        }

        return response()->successJson(['data' => $section, 'message' => 'Section updated successfully.']);
    }

    public function destroy($id)
    {
        $deleted = $this->sectionService->delete($id);

        if (!$deleted) {
            return response()->errorJson(['message' => 'Section not found.'], 404);
        }

        return response()->successJson(['message' => 'Section deleted successfully.']);
    }
}
