<!DOCTYPE html>
<html>
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<head>
    <title>Add Student</title>
</head>
<body>

<h1>Add Student</h1>

<form method="POST" action="/students">
    @csrf

    <p>
        Admission No<br>
        <input type="text" name="admission_no">
    </p>

    <p>
        First Name<br>
        <input type="text" name="first_name">
    </p>

    <p>
        Last Name<br>
        <input type="text" name="last_name">
    </p>

    <p>
        Date of Birth<br>
        <input type="date" name="date_of_birth">
    </p>

    <p>
        Gender<br>
        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </p>

    <p>
        Mobile<br>
        <input type="text" name="mobile">
    </p>

    <button type="submit">Save Student</button>
</form>

</body>
</html>