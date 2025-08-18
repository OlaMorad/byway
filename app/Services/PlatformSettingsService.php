<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Category;
use App\Models\Setting;

class PlatformSettingsService
{
    // ======================
    // Categories Management
    // ======================

    // جلب كل الكاتيجوريز
    public function getAllCategories()
    {
        $categories = Category::select('id', 'name')->get();

        return ApiResponse::sendResponse(200, 'Categories retrieved successfully', $categories);
    }

    // إضافة كاتيجوري جديدة
    public function addCategory($name)
    {
        $category = Category::create([
            'name' => $name,
        ]);

        return ApiResponse::sendResponse(201, 'Category added successfully', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    // تعديل اسم الكاتيجوري
    public function editCategory($categoryId, $newName)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category not found');
        }

        $category->name = $newName;
        $category->save();

        return ApiResponse::sendResponse(200, 'Category updated successfully', [
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    // حذف كاتيجوري
    public function deleteCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category not found');
        }

        $category->delete();

        return ApiResponse::sendResponse(200, 'Category deleted successfully');
    }

    // ======================
    // Settings Management
    // ======================

    // عرض الإعدادات
    public function getSettings()
    {
        $settings = Setting::firstOrCreate([], [
            'commission' => 15.00, // القيمة الافتراضية
            'withdrawal' => 50.00, // القيمة الافتراضية
        ]);

        return ApiResponse::sendResponse(200, 'Settings retrieved successfully', [
            'commission' => $settings->commission,
            'withdrawal' => $settings->withdrawal,
        ]);
    }

    // تعديل الإعدادات
    public function updateSettings($commission, $withdrawal)
    {
        $settings = Setting::updateOrCreate(
            [], // شرط البحث، أي سجل موجود
            [
                'commission' => $commission,
                'withdrawal' => $withdrawal,
            ]
        );
        
        $settings->commission = $commission;
        $settings->withdrawal = $withdrawal;
        $settings->save();

        return ApiResponse::sendResponse(200, 'Settings updated successfully', [
            'commission' => $settings->commission,
            'withdrawal' => $settings->withdrawal,
        ]);
    }
}
