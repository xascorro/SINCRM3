<?php
include('./lib/my_functions.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SINCRM</title>

    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#334155", 
                        "primary-dark": "#1e293b", 
                        "primary-light": "#64748b",
                        "secondary": "#3b82f6", 
                        "oceanic": "#cbd5e1",
                        "surface": "#f8fafc",
                        "on-surface": "#1e293b",
                        "error": "#ef4444",
                    },
                    fontFamily: { lexend: ["Lexend", "sans-serif"] }
                },
            },
        }
    </script>

    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #f1f5f9; overflow-x: hidden; color: #1e293b; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .submenu-closed { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.3s ease-in-out; }
        .submenu-open { max-height: 500px; opacity: 1; transition: all 0.3s ease-in-out; }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .oceanic-gradient { background: linear-gradient(135deg, #64748b 0%, #334155 100%); }

        .nav-item-active {
            background: rgba(255, 255, 255, 0.1) !important;
            border-left: 4px solid #3b82f6 !important;
            color: #fff !important;
        }

        .sub-nav-item-active {
            border-left: 4px solid #3b82f6 !important;
            color: #fff !important;
            background: transparent !important;
        }

        /* FIX CAPAS SWEETALERT2 */
        .swal2-container { z-index: 99999 !important; }

        /* ESTILO SELECTS v3.0 (Excluyendo componentes externos como SWAL) */
        select:not([class*="swal2"]), .v3-select-fix {
            display: block !important;
            width: 100% !important;
            padding: 0.875rem 1.25rem !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            color: #334155 !important;
            background-color: #f8fafc !important;
            border: 1px solid #f1f5f9 !important;
            border-radius: 1rem !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 1rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            padding-right: 3rem !important;
            transition: all 0.2s ease !important;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.03) !important;
        }

        select:not([class*="swal2"]):focus, .v3-select-fix:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
            background-color: #fff !important;
            outline: none !important;
        }
    </style>

    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="antialiased bg-[#f1f5f9]">
<div id="wrapper" class="flex min-h-screen">
