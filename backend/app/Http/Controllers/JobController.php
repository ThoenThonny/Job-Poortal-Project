<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Job;

class JobController extends Controller
{
    // ✅ List all jobs
    public function index()
    {
        try {
            $jobs = Job::all();
            return response()->json($jobs, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch jobs: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Store new job
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'level' => 'required|string|max:255',
                'skill' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'salary' => 'required|numeric',
                'location' => 'required|string|max:255',
                'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'job_description' => 'required|string',
                'requirements' => 'required|string',
                'responsibilities' => 'required|string',
                'benefits' => 'required|string',
                'experience' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $posterUrl = null;

            // ✅ Handle image upload
            if ($request->hasFile('poster')) {
                $file = $request->file('poster');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('uploads/posters');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $file->move($uploadPath, $fileName);

                // Generate full URL
                $posterUrl = url('uploads/posters/' . $fileName);
            }
            
            $job = Job::create([
                'title' => $request->title,
                'category' => $request->category,
                'company' => $request->company,
                'level' => $request->level,
                'skill' => $request->skill,
                'type' => $request->type,
                'salary' => $request->salary,
                'location' => $request->location,
                'poster' => $posterUrl,
                'job_description' => $request->job_description,
                'requirements' => $request->requirements,
                'responsibilities' => $request->responsibilities,
                'benefits' => $request->benefits,
                'experience' => $request->experience,
            ]);

            return response()->json([
                'message' => 'Job created successfully',
                'data' => $job
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create job: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Get job by ID
    public function show($id)
    {
        try {
            $job = Job::find($id);

            if (!$job) {
                return response()->json(['message' => 'Job not found'], 404);
            }

            return response()->json($job, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch job: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Update job with POST method
    public function update(Request $request, $id)
    {
        try {
            $job = Job::find($id);

            if (!$job) {
                return response()->json(['message' => 'Job not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string|max:255',
                'company' => 'sometimes|required|string|max:255',
                'level' => 'sometimes|required|string|max:255',
                'skill' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|max:255',
                'salary' => 'sometimes|required|numeric',
                'location' => 'sometimes|required|string|max:255',
                'poster' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'job_description' => 'sometimes|required|string',
                'requirements' => 'sometimes|required|string',
                'responsibilities' => 'sometimes|required|string',
                'benefits' => 'sometimes|required|string',
                'experience' => 'sometimes|required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // ✅ Handle image re-upload
            if ($request->hasFile('poster')) {
                // Delete old image if exists
                if ($job->poster) {
                    $oldPath = public_path('uploads/posters/' . basename($job->poster));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $file = $request->file('poster');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('uploads/posters');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $file->move($uploadPath, $fileName);
                $job->poster = url('uploads/posters/' . $fileName);
            }

            // Update other fields
            $job->fill($request->except('poster'));
            $job->save();

            return response()->json([
                'message' => 'Job updated successfully',
                'data' => $job
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update job: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Delete job
    public function destroy($id)
    {
        try {
            $job = Job::find($id);

            if (!$job) {
                return response()->json(['message' => 'Job not found'], 404);
            }

            // Delete image file if exists
            if ($job->poster) {
                $oldPath = public_path('uploads/posters/' . basename($job->poster));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $job->delete();

            return response()->json(['message' => 'Job deleted successfully'], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete job: ' . $e->getMessage()
            ], 500);
        }
    }
}