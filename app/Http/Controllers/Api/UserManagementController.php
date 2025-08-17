<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserManagementServices;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(
        protected UserManagementServices $userManagementServices
    ) {}
    // عرض كل المستخدمين
    public function index()
    {
        return $this->userManagementServices->allRegisteredUsers();
    }
    // عرض اليوزر بروفايل
    public function show($id)
    {
        return $this->userManagementServices->userProfile($id);
    }

    public function toggleStatus($id)
    {
        return $this->userManagementServices->toggleUserStatus($id);
    }
    // حذف المستخدمين
    public function destroy($id)
    {
        return $this->userManagementServices->deleteUser($id);
    }
    // عرض كل الاساتذة
    public function allInstructors()
    {
        return $this->userManagementServices->allInstructors();
    }
}
