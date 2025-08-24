# Resources Usage Examples - أمثلة على استخدام الـ Resources

## Overview
This file contains examples of how to use the Course and Lesson Resources in the API responses.

---

## 🎯 CourseResource Examples

### 1. Single Course Response
When you call `GET /api/instructor/course-management/courses/{id}`:

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Web Development",
    "description": "Learn the fundamentals of web development including HTML, CSS, and JavaScript",
    "image_url": "/storage/courses/images/web-dev-course.jpg",
    "video_url": "/storage/courses/videos/web-dev-intro.mp4",
    "status": "published",
    "status_text": "Published",
    "price": "99.99",
    "formatted_price": "99.99 $",
    "category": {
      "id": 1,
      "name": "Web Development"
    },
    "instructor": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "lessons_count": 12,
    "lessons": [
      {
        "id": 1,
        "title": "HTML Basics",
        "description": "Introduction to HTML markup language",
        "video_url": "/storage/lessons/videos/html-basics.mp4",
        "video_duration": 1800,
        "formatted_duration": "30:00",
        "materials": [],
        "materials_count": 0,
        "order": 1,
        "course_id": 1,
        "created_at": "2024-01-15 10:00:00",
        "updated_at": "2024-01-15 10:00:00"
      }
    ],
    "reviews_count": 25,
    "average_rating": 4.8,
    "enrollments_count": 150,
    "created_at": "2024-01-10 09:00:00",
    "updated_at": "2024-01-15 15:30:00"
  }
}
```

### 2. Course List Response
When you call `GET /api/instructor/course-management/courses`:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Web Development",
      "description": "Learn the fundamentals of web development",
      "image_url": "/storage/courses/images/web-dev-course.jpg",
      "video_url": "/storage/courses/videos/web-dev-intro.mp4",
      "status": "published",
      "status_text": "Published",
      "price": "99.99",
      "formatted_price": "99.99 $",
      "category": {
        "id": 1,
        "name": "Web Development"
      },
      "instructor": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "lessons_count": 12,
      "reviews_count": 25,
      "average_rating": 4.8,
      "enrollments_count": 150,
      "created_at": "2024-01-10 09:00:00",
      "updated_at": "2024-01-15 15:30:00"
    },
    {
      "id": 2,
      "title": "Advanced JavaScript",
      "description": "Master JavaScript programming concepts",
      "image_url": "/storage/courses/images/js-advanced.jpg",
      "video_url": "/storage/courses/videos/js-intro.mp4",
      "status": "draft",
      "status_text": "Draft",
      "price": "149.99",
      "formatted_price": "149.99 $",
      "category": {
        "id": 1,
        "name": "Web Development"
      },
      "instructor": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "lessons_count": 0,
      "reviews_count": 0,
      "average_rating": 0,
      "enrollments_count": 0,
      "created_at": "2024-01-20 14:00:00",
      "updated_at": "2024-01-20 14:00:00"
    }
  ],
  "meta": {
    "total": 2,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1,
    "from": 1,
    "to": 2
  },
  "links": {
    "first": "/api/instructor/course-management/courses?page=1",
    "last": "/api/instructor/course-management/courses?page=1",
    "prev": null,
    "next": null
  }
}
```

---

## 🎓 LessonResource Examples

### 1. Single Lesson Response
When you call `GET /api/instructor/course-management/courses/{courseId}/lessons/{lessonId}`:

```json
{
  "data": {
    "id": 1,
    "title": "HTML Basics",
    "description": "Introduction to HTML markup language and basic structure",
    "video_url": "/storage/lessons/videos/html-basics.mp4",
    "video_duration": 1800,
    "formatted_duration": "30:00",
    "materials": [
      {
        "name": "HTML Cheat Sheet",
        "type": "pdf",
        "url": "/storage/lessons/materials/html-cheatsheet.pdf"
      },
      {
        "name": "Practice Exercises",
        "type": "document",
        "url": "/storage/lessons/materials/html-exercises.docx"
      }
    ],
    "materials_count": 2,
    "order": 1,
    "course_id": 1,
    "course": {
      "id": 1,
      "title": "Introduction to Web Development"
    },
    "completion_status": "Completed",
    "completion_date": "2024-01-16 11:30:00",
    "created_at": "2024-01-15 10:00:00",
    "updated_at": "2024-01-15 10:00:00"
  }
}
```

### 2. Lesson List Response
When you call `GET /api/instructor/course-management/courses/{courseId}/lessons`:

```json
{
  "data": [
    {
      "id": 1,
      "title": "HTML Basics",
      "description": "Introduction to HTML markup language",
      "video_url": "/storage/lessons/videos/html-basics.mp4",
      "video_duration": 1800,
      "formatted_duration": "30:00",
      "materials": [],
      "materials_count": 0,
      "order": 1,
      "course_id": 1,
      "created_at": "2024-01-15 10:00:00",
      "updated_at": "2024-01-15 10:00:00"
    },
    {
      "id": 2,
      "title": "CSS Fundamentals",
      "description": "Learn CSS styling and layout",
      "video_url": "/storage/lessons/videos/css-fundamentals.mp4",
      "video_duration": 2400,
      "formatted_duration": "40:00",
      "materials": [
        {
          "name": "CSS Reference Guide",
          "type": "pdf",
          "url": "/storage/lessons/materials/css-reference.pdf"
        }
      ],
      "materials_count": 1,
      "order": 2,
      "course_id": 1,
      "created_at": "2024-01-15 11:00:00",
      "updated_at": "2024-01-15 11:00:00"
    }
  ],
  "meta": {
    "total": 2,
    "course_id": 1,
    "total_duration": 4200,
    "formatted_total_duration": "1 hours and 10 minutes"
  }
}
```

---

## 🔧 How to Use Resources in Controllers

### 1. In CourseManagementController

```php
use App\Http\Resources\Api\CourseResource;
use App\Http\Resources\Api\CourseCollection;

class CourseManagementController extends Controller
{
    public function index()
    {
        $courses = Course::where('user_id', Auth::id())
            ->with(['category', 'lessons'])
            ->orderBy('created_at', 'desc')
            ->get();

        return new CourseCollection($courses);
    }

    public function show($id)
    {
        $course = Course::where('user_id', Auth::id())
            ->with(['category', 'lessons' => function($query) {
                $query->orderBy('order');
            }])
            ->findOrFail($id);

        return new CourseResource($course);
    }

    public function store(Request $request)
    {
        // ... validation and creation logic

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => new CourseResource($course->load('category'))
        ], 201);
    }
}
```

### 2. In LessonManagementController

```php
use App\Http\Resources\Api\LessonResource;
use App\Http\Resources\Api\LessonCollection;

class LessonManagementController extends Controller
{
    public function index($courseId)
    {
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        $lessons = $course->lessons()->orderBy('order')->get();

        return new LessonCollection($lessons);
    }

    public function show($courseId, $lessonId)
    {
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        $lesson = $course->lessons()->findOrFail($lessonId);

        return new LessonResource($lesson);
    }

    public function store(Request $request, $courseId)
    {
        // ... validation and creation logic

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'data' => new LessonResource($lesson)
        ], 201);
    }
}
```

---

## 📊 Resource Features

### CourseResource Features:
- **Basic Info**: ID, title, description, image, video
- **Status**: Current status with English text (Draft, Pending Review, Published)
- **Pricing**: Price with formatted display
- **Relationships**: Category, instructor, lessons
- **Statistics**: Lessons count, reviews count, average rating, enrollments count
- **Timestamps**: Created and updated dates

### LessonResource Features:
- **Basic Info**: ID, title, description, video
- **Duration**: Video duration in seconds and formatted display
- **Materials**: Array of additional materials with metadata
- **Ordering**: Lesson order within course
- **Completion**: Student completion status and date
- **Relationships**: Course information

### Collection Features:
- **Pagination**: Page information and navigation links
- **Metadata**: Total counts and additional statistics
- **Consistent Format**: All items follow the same structure

---

## 🚀 Benefits of Using Resources

1. **Consistent API Responses**: All endpoints return data in the same format
2. **Data Transformation**: Automatically format dates, numbers, and relationships
3. **Performance**: Load relationships only when needed with `whenLoaded()`
4. **Maintainability**: Change data format in one place
5. **Security**: Hide sensitive information automatically
6. **Flexibility**: Easy to add/remove fields or modify formatting

---

## 📝 Notes

- **Status Values**: Use `draft`, `pending`, or `published` for course status
- **File Uploads**: Images and videos are stored in `storage/app/public`
- **Relationships**: Use `with()` to eager load relationships for better performance
- **Validation**: Always validate input data before creating/updating resources
- **Authorization**: Ensure users can only access their own courses and lessons
