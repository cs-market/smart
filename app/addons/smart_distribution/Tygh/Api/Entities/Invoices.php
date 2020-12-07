<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Invoices extends AEntity
{
    public function index($id = 0, $params = array())
    {
        if (fn_is_order_allowed($id, $this->auth)) {
            $data = array();

            $pdf_file = fn_print_order_invoices($id, array(
                'pdf' => true, 'save' => true)
            );

            $data = fn_get_contents($pdf_file);
            $filename=basename($pdf_file);
            header("Content-disposition: attachment; filename=\"$filename\"");
            header('Content-type: application/pdf');
            echo $data;
            fn_rm($pdf_file);
            exit;
        } else {
            return array(
                'status' => Response::STATUS_FORBIDDEN,
                'data' => $data
            );
        }
    }

    public function create($params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function update($id, $params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function delete($id)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function privileges()
    {
        return array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'index'  => true
        );
    }
    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }
}
