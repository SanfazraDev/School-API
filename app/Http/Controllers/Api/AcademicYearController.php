<?php

namespace App\Http\Controllers\Api;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use App\Http\Requests\AcademicYear\CreateRequest;
use App\Http\Requests\AcademicYear\UpdateRequest;
use Illuminate\Support\Facades\Log;

class AcademicYearController extends Controller
{
    public function index()
    {
        return ResponseHelper::success(AcademicYearResource::collection(AcademicYear::all()), 'Academic years retrieved successfully', 200);
    }

    public function store(CreateRequest $request)
    {
        $request = $request->validated();

        $request['name'] = $request['year_start'] . '/' . $request['year_end'];

        try {
            
            DB::beginTransaction();

            $academicYear = AcademicYear::query()->create($request);

            DB::commit();

            return ResponseHelper::success(new AcademicYearResource($academicYear), 'Academic year created successfully', 201);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error creating academic year: ' . $e->getMessage());
            return ResponseHelper::error('Error', 'Error creating academic year', 500);

        }
    }

    public function show(AcademicYear $academicYear)
    {
        return ResponseHelper::success(new AcademicYearResource($academicYear), 'Academic year retrieved successfully', 200);
    }

    public function update(UpdateRequest $request, AcademicYear $academicYear)
    {
        $validatedData = $request->validated();

        try {
            
            DB::beginTransaction();

            $academicYear->update($validatedData);

            DB::commit();

            return ResponseHelper::success(new AcademicYearResource($academicYear), 'Academic year updated successfully', 200);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error updating academic year: ' . $e->getMessage());
            return ResponseHelper::error('Error', 'Error updating academic year', 500);

        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        try {
            
            DB::beginTransaction();

            $academicYear->delete();

            DB::commit();

            return ResponseHelper::success(null, 'Academic year deleted successfully', 200);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error deleting academic year: ' . $e->getMessage());
            return ResponseHelper::error('Error', 'Error deleting academic year', 500);

        }
    }
}
