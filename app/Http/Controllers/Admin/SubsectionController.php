<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SubsectionService;
use Illuminate\Http\Request;

class SubsectionController extends Controller
{
    protected $subsectionService;

    public function __construct(SubsectionService $subsectionService)
    {
        $this->subsectionService = $subsectionService;
    }

    public function index()
    {
        $subsections = $this->subsectionService->all();
        return response()->successJson($subsections);
    }

    public function show($id)
    {
        $subsection = $this->subsectionService->find($id);
        if (!$subsection) {
            return response()->errorJson(['message' => 'Not found'], 404);
        }
        return response()->successJson($subsection);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:subsections,slug',
            'section_id' => 'required|exists:sections,id',
            'order' => 'nullable|integer|min:0',
            'files' => 'required|array|min:1',
            'files.*.file_id' => 'required|integer|exists:subsection_files,id',
            'files.*.content' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $subsection = $this->subsectionService->create($validated);
        return response()->successJson($subsection, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:subsections,slug,' . $id,
            'section_id' => 'sometimes|required|exists:sections,id',
            'order' => 'sometimes|nullable|integer|min:0',
            'files' => 'sometimes|required|array|min:1',
            'files.*.file_id' => 'sometimes|required|integer|exists:subsection_files,id',
            'files.*.content' => 'sometimes|required|string',
            'description' => 'nullable|string',
        ]);
        $subsection = $this->subsectionService->update($id, $validated);
        if (!$subsection) {
            return response()->errorJson(['message' => 'Not found'], 404);
        }
        return response()->successJson($subsection);
    }

    public function destroy($id)
    {
        $deleted = $this->subsectionService->delete($id);
        if (!$deleted) {
            return response()->errorJson(['message' => 'Not found'], 404);
        }
        return response()->successJson(['message' => 'Deleted successfully']);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'type' => 'required|string',
        ]);
        $result = $this->subsectionService->attachFile($request->file('file'), $request->input('type'));
        if (!$result) {
            return response()->errorJson(['message' => 'Subsection not found'], 404);
        }
        return response()->successJson($result, 201);
    }
}
