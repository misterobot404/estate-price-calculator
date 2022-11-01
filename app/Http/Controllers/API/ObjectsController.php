<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ObjectsController extends Controller
{
    public function parseFileOfObjects()
    {
        $spreadsheet = IOFactory::load(request('file_of_objects'));

        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        array_shift($data);

        // Формируем окончательный ответ в нужном формате
        return response()->json([
            'objects' => $data,
            'status' => 200
        ], 200);
    }
}
