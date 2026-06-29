<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/push-panel', function() {
    return view('push_panel');
});

Route::post('/push-panel/execute', function(\Illuminate\Http\Request $request) {
    $action = $request->input('action');
    $token = $request->input('token');
    
    // Kill any existing git push
    shell_exec('pkill -f "git push"');
    
    if ($action === 'export') {
        $output = shell_exec('php ' . base_path('export.php') . ' 2>&1');
        return ['success' => true, 'output' => $output];
    }
    
    if ($action === 'commit') {
        shell_exec('git add ' . base_path('index.html') . ' ' . base_path('resources/views/welcome.blade.php') . ' 2>&1');
        $output = shell_exec('git commit -m "feat: export portfolio without Vue.js and Docker" 2>&1');
        return ['success' => true, 'output' => $output];
    }
    
    if ($action === 'push') {
        if (!$token) {
            return ['success' => false, 'error' => 'Token is required for pushing.'];
        }
        // Construct the authenticated git URL
        $repoUrl = "https://suensereypanha-maker:{$token}@github.com/suensereypanha-maker/My-profile.git";
        
        // Update remote URL temporarily
        shell_exec("git remote set-url origin " . escapeshellarg($repoUrl));
        
        // Run git push
        $output = shell_exec('git push origin main 2>&1');
        
        // Restore public/clean remote URL
        shell_exec('git remote set-url origin https://github.com/suensereypanha-maker/My-profile.git');
        
        if (strpos($output, 'Everything up-to-date') !== false || strpos($output, 'To github.com') !== false || strpos($output, 'master -> master') !== false || strpos($output, 'main -> main') !== false) {
            return ['success' => true, 'output' => $output];
        } else {
            return ['success' => false, 'error' => $output];
        }
    }
    
    return ['success' => false, 'error' => 'Invalid action.'];
});
