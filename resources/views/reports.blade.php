<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
</head>
<body>
    <h1> General Statistics</h1>
    <ul>
        <li>Instructors: {{ $general['instructors'] }}</li>
        <li>Learners: {{ $general['learners'] }}</li>
        <li>Courses: {{ $general['courses'] }}</li>
        <li>Total Earnings: ${{ $general['earnings'] }}</li>
    </ul>

    <h1> Courses Average Ratings</h1>
    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Average Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
                <tr>
                    <td>{{ $course['course_name'] }}</td>
                    <td>{{ $course['avg_rating'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
