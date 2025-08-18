<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function __construct(
        protected PlatformSettingsService $settingsService
    ) {}

    // جلب كل الكاتيجوريز
    public function index()
    {
        return $this->settingsService->getAllCategories();
    }

    // إضافة كاتيجوري جديدة
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return $this->settingsService->addCategory($request->name);
    }

    // تعديل اسم الكاتيجوري
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return $this->settingsService->editCategory($id, $request->name);
    }

    // حذف كاتيجوري
    public function destroy($id)
    {
        return $this->settingsService->deleteCategory($id);
    }


    // عرض الإعدادات
    public function showSettings()
    {
        return $this->settingsService->getSettings();
    }

    // تعديل الإعدادات
    public function editSettings(Request $request)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0',
            'withdrawal' => 'required|numeric|min:0',
        ]);

        return $this->settingsService->updateSettings($request->commission, $request->withdrawal);
    }
}
