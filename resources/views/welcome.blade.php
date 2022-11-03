<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    </head>
    <style>
       #app {
        font-family: Avenir, Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: #2c3e50;
        margin-top: 60px;
        }
        
        html, body, #map, #app {
        height: 100%;
        margin: 0;
        padding: 0;
        }
   .map,#map {
        position: relative;
    }
    .map-control {
        color: white;
        width: 25.075px;
        height: 25.075px;
        background-color: rgba(0, 60, 136, 0.7);
        position: absolute;
    }
    .flex-row{
        display: flex;
        flex-direction: row;
    }
    </style>
    <body>
     <div id="app">
         <map-component></map-component>
     </div>           
     <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
