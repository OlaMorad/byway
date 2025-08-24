<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddAdminRequest;
use App\Http\Requests\Api\AddInstructorRequest;
use App\Http\Requests\Api\UpdateInstructorRequest;
use App\Services\UserManagementServices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    // تغيير حالة الحساب
    public function toggleStatus($id)
    {
        return $this->userManagementServices->toggleUserStatus($id);
    }
    // حذف المستخدمين
    public function destroy($id)
    {
        return $this->userManagementServices->deleteUser($id);
    }
    // البحث عن مستخدم
    public function searchUsers(Request $request)
    {
        $key = $request->query('key');
        return $this->userManagementServices->searchUsers($key);
    }
    // تعديل المستخدم
    public function updateUser(Request $request, $userId)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', Rule::in(['learner', 'instructor'])],
        ]);

        return $this->userManagementServices->updateUser($userId, $validated);
    }
    // عرض كل الاساتذة
    public function allInstructors()
    {
        return $this->userManagementServices->allInstructors();
    }
    // اضافة استاذ
    public function addInstructor(AddInstructorRequest $request)
    {
        $data = $request->validated();
        return $this->userManagementServices->addInstructor($data);
    }
    // تعديل معلومات استاذ
    public function updateInstructorProfile(UpdateInstructorRequest $request, $id)
    {
        return $this->userManagementServices->updateInstructor($request->validated(), $id);
    }
    // بحث
    public function searchInstructors(Request $request)
    {
        $key = $request->query('key');
        return $this->userManagementServices->searchInstructors($key);
    }

    public function addAdmin(AddAdminRequest $request)
    {
        return $this->userManagementServices->addAdmin($request->validated());
    }
}
