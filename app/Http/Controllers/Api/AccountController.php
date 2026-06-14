<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
  public function index(Request $request)
  {
    $perPage = $request->query('per_page', 10);
    $search = $request->query('search');
    $sort = $request->query('sort', 'code');
    $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = Account::with('group');
    if ($search) {
      $query->where(fn ($q) => $q
        ->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($search) . '%'])
        ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']));
    }

    if (!in_array($sort, ['id', 'code', 'name', 'type', 'active', 'created_at'])) {
      $sort = 'code';
    }

    $records = $query->orderBy($sort, $direction)->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'message' => 'Account list fetched successfully',
      'data' => $records->items(),
      'meta' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        'per_page' => $records->perPage(),
        'total' => $records->total(),
        'sort' => $sort,
        'direction' => $direction,
      ],
    ]);
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'code' => 'required|string|max:25|unique:acc,code',
      'name' => 'required|string|max:125',
      'accgrp_id' => 'nullable|integer|exists:accgrp,id',
      'type' => 'required|string|max:25',
      'normal_balance' => 'required|in:DEBIT,CREDIT',
      'is_cash' => 'boolean',
      'active' => 'boolean',
    ]);
    $data['created_by'] = auth()->id() ?? 1;
    $data['updated_by'] = auth()->id() ?? 1;

    $account = Account::create($data);

    return response()->json(['status' => 'success', 'message' => 'Account created successfully', 'data' => $account], 201);
  }

  public function show($id)
  {
    $account = Account::with('group')->find($id);
    if (!$account) return response()->json(['status' => 'error', 'message' => 'Account not found', 'data' => null], 404);

    return response()->json(['status' => 'success', 'message' => 'Account fetched successfully', 'data' => $account]);
  }

  public function update(Request $request, $id)
  {
    $account = Account::find($id);
    if (!$account) return response()->json(['status' => 'error', 'message' => 'Account not found', 'data' => null], 404);

    $data = $request->validate([
      'code' => 'sometimes|required|string|max:25|unique:acc,code,' . $account->id,
      'name' => 'sometimes|required|string|max:125',
      'accgrp_id' => 'nullable|integer|exists:accgrp,id',
      'type' => 'sometimes|required|string|max:25',
      'normal_balance' => 'sometimes|required|in:DEBIT,CREDIT',
      'is_cash' => 'boolean',
      'active' => 'boolean',
    ]);
    $data['updated_by'] = auth()->id() ?? 1;
    $account->update($data);

    return response()->json(['status' => 'success', 'message' => 'Account updated successfully', 'data' => $account]);
  }

  public function destroy($id)
  {
    $account = Account::find($id);
    if (!$account) return response()->json(['status' => 'error', 'message' => 'Account not found', 'data' => null], 404);

    $account->active = false;
    $account->save();

    return response()->json(['status' => 'success', 'message' => 'Account deactivated successfully', 'data' => $account]);
  }
}
