<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>

<h1>Edit Student</h1>

<form method="POST" action="/students/{{ $student->id }}">
    @csrf
    @method('PUT')

    <p>
        Admission No<br>
        <input type="text" name="admission_no" value="{{ $student->admission_no }}">
    </p>

    <p>
        First Name<br>
        <input type="text" name="first_name" value="{{ $student->first_name }}">
    </p>

    <p>
        Last Name<br>
        <input type="text" name="last_name" value="{{ $student->last_name }}">
    </p>

    <p>
        Date of Birth<br>
        <input type="date" name="date_of_birth" value="{{ $student->date_of_birth }}">
    </p>

    <p>
        Gender<br>
        <select name="gender">
            <option value="Male" {{ $student->gender == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $student->gender == 'Female' ? 'selected' : '' }}>Female</option>
        </select>
    </p>

    <p>
        Mobile<br>
        <input type="text" name="mobile" value="{{ $student->mobile }}">
    </p>

    <button type="submit">Update Student</button>
</form>

</body>
</html>