<!DOCTYPE html>
<html>
<head>
    <title>Route Control</title>
</head>
<body>
    <h1>Route Control Page</h1>

    <form action="{{ route('route.toggle', ['route' => 'specific-route']) }}" method="post">
        @csrf
        <input type="checkbox" name="status" value="enabled"> Enable Specific Route<br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
