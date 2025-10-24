<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== PUBLIC ROUTES ====================

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Job Routes
Route::get('/jobs', [JobController::class, 'index']);        // ✅ Get all jobs
Route::get('/jobs/{id}', [JobController::class, 'show']);    // ✅ Get job by ID

// ==================== PROTECTED ROUTES (Require Authentication) ====================

Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Job CRUD Operations - Using POST for update
    Route::post('/jobs', [JobController::class, 'store']);           // ✅ Create new job
    Route::post('/jobs/{id}/update', [JobController::class, 'update']);  // ✅ Update job using POST
    
    // Keep DELETE as is
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);  // ✅ Delete job

});

// ==================== ADMIN ONLY ROUTES ====================

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Admin Authentication Check
    Route::get('/admin/check', [AuthController::class, 'adminCheck']);
    
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to Admin Dashboard',
            'data' => [
                'total_jobs' => \App\Models\Job::count(),
                'total_users' => \App\Models\User::count(),
            ]
        ]);
    });
    
    // Get all users
    Route::get('/admin/users', function () {
        $users = \App\Models\User::select('id', 'name', 'email', 'role', 'created_at')
            ->latest()
            ->get();
            
        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users,
            'count' => $users->count()
        ]);
    });
    
    // Admin Job Management
    Route::get('/admin/jobs', function () {
        $jobs = \App\Models\Job::latest()->get();
        return response()->json([
            'message' => 'Admin jobs retrieved successfully',
            'data' => $jobs,
            'count' => $jobs->count()
        ]);
    });
    
    // Admin delete job
    Route::delete('/admin/jobs/{id}', function ($id) {
        try {
            $job = \App\Models\Job::find($id);

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

            return response()->json([
                'message' => 'Job deleted by admin successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete job: ' . $e->getMessage()
            ], 500);
        }
    });
});

// ==================== TEST ROUTES ====================

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
    ]);
});

// ==================== FALLBACK ROUTE ====================

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found'
    ], 404);
});