<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ApiToken;

class DeveloperToolsController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::latest()->get();
        return view('DeveloperTools', compact('tokens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tokenStr = 'sk_' . Str::random(32);

        ApiToken::create([
            'name' => $request->name,
            'token' => $tokenStr,
            'is_active' => true,
        ]);

        return redirect('/developer-tools')->with('success', 'API Token generated successfully.');
    }

    public function toggle(Request $request, $id)
    {
        $token = ApiToken::findOrFail($id);
        $token->update(['is_active' => !$token->is_active]);

        return redirect('/developer-tools')->with('success', 'Token status updated.');
    }

    public function destroy($id)
    {
        $token = ApiToken::findOrFail($id);
        $token->delete();

        return redirect('/developer-tools')->with('success', 'Token deleted successfully.');
    }

    public function testGitLabConnection(\App\Services\GitLabService $gitlab)
    {
        $token = env('GITLAB_TOKEN');
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'GitLab Token (GITLAB_TOKEN) is not configured in the .env file.'
            ]);
        }

        $projects = $gitlab->getProjects();
        if (is_array($projects) && (!empty($projects) || count($projects) >= 0)) {
            // GitLab returns an array on successful auth.
            return response()->json([
                'success' => true,
                'message' => 'Connection to GitLab successful! Accessible projects: ' . count($projects)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to connect to GitLab. Please verify your token.'
        ]);
    }

    public function getGitLabProjects(\App\Services\GitLabService $gitlab)
    {
        $projects = $gitlab->getProjects();
        if (!is_array($projects)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve repositories. Ensure your token is correct.'
            ]);
        }

        $formatted = collect($projects)->map(function ($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name_with_namespace'] ?? $p['name'],
                'last_activity' => isset($p['last_activity_at']) ? \Carbon\Carbon::parse($p['last_activity_at'])->format('Y-m-d H:i') : 'N/A'
            ];
        })->values();

        return response()->json([
            'success' => true,
            'projects' => $formatted
        ]);
    }

    public function syncGitLabCommits(Request $request, \App\Services\GitLabService $gitlab)
    {
        $projectId = $request->input('project_id');
        if (empty($projectId)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a repository first.'
            ]);
        }

        $result = $gitlab->syncCommits($projectId);
        return response()->json($result);
    }
}
