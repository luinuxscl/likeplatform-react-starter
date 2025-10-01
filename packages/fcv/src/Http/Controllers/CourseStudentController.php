<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Like\Fcv\Http\Requests\CourseStudentImportRequest;
use Like\Fcv\Models\Course;
use Like\Fcv\Services\CourseStudentImportService;

class CourseStudentController extends Controller
{
    public function __construct(
        protected CourseStudentImportService $importService
    ) {}

    public function import(CourseStudentImportRequest $request, Course $course): JsonResponse
    {
        $file = $request->file('file');
        
        $result = $this->importService->import($course, $file);

        return response()->json([
            'success' => true,
            'message' => 'Importación completada',
            'summary' => $result,
        ]);
    }

    public function attach(Course $course, int $personId): RedirectResponse
    {
        $course->students()->syncWithoutDetaching([$personId]);

        return redirect()
            ->route('fcv.courses.show', $course)
            ->with('flash.success', 'Alumno vinculado al curso correctamente.');
    }

    public function detach(Course $course, int $personId): RedirectResponse
    {
        $course->students()->detach($personId);

        return redirect()
            ->route('fcv.courses.show', $course)
            ->with('flash.success', 'Alumno desvinculado del curso.');
    }
}
