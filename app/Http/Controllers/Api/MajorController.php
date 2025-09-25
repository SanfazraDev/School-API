<?php

namespace App\Http\Controllers\Api;

use App\Models\Major;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\MajorResource;
use App\Http\Requests\Major\CreateRequest;
use App\Http\Requests\Major\UpdateRequest;
use Illuminate\Support\Facades\Log;

class MajorController extends Controller
{
    public function index(Request $request)
    {
        $majors = Major::query()->latest()->get();
        return ResponseHelper::success(MajorResource::collection($majors));
    }

    public function show(Major $major)
    {
        return ResponseHelper::success(new MajorResource($major));
    }

    public function store(CreateRequest $request)
    {
        $request = $request->validated();

        $codeUppercase = strtoupper($request['code']);
        $request['code'] = $codeUppercase;

        try {
            
            DB::beginTransaction();

            $major = Major::query()->create($request);

            DB::commit();

            return ResponseHelper::success(new MajorResource($major), 'Major created successfully');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error creating major: ' . $e->getMessage());
            return ResponseHelper::error('Error creating major', 500);

        }

    }

    public function update(UpdateRequest $request, Major $major)
    {
        $request = $request->validated();

        $codeUppercase = strtoupper($request['code']);
        $request['code'] = $codeUppercase;

        try {
            
            DB::beginTransaction();

            $major->update($request);

            DB::commit();

            return ResponseHelper::success(new MajorResource($major), 'Major updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error updating major: ' . $e->getMessage());
            return ResponseHelper::error('Error updating major', 500);

        }
    }

    public function destroy(Major $major)
    {
        try {
            
            DB::beginTransaction();

            $major->delete();

            DB::commit();

            return ResponseHelper::success(null, 'Major deleted successfully');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error deleting major: ' . $e->getMessage());
            return ResponseHelper::error('Error deleting major', 500);

        }
    }
}
