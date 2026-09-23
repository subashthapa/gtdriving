<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Booking Calendar</title>
    @vite('resources/js/app.js')
</head>
<body class="font-sans bg-gray-50 text-gray-900">
    <div id="app"> 
        
        <booking-calendar
            :instructors="{{ $instructors }}"
            :confirmed="{{ $confirmed }}"
        ></booking-calendar>
    </div>
</body>
</html>
