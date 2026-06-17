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
}
