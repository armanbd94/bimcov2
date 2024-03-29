<?php 
//IMAGE FOLDER PATH
define('LOGO_PATH','logo/');
define('USER_PHOTO_PATH','user/');
define('MATERIAL_IMAGE_PATH','material/');
define('PRODUCT_IMAGE_PATH','product/');
define('EMPLOYEE_NID_PHOTO','employee/');
define('EMPLOYEE_IMAGE_PATH','employee/');



define('GENDER_LABEL',[
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Male</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Female</span>',
    '3' => '<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Other</span>',
]);
define('STATUS',['1' => 'Active', '2' => 'Inactive']);
define('PRODUCT_TYPE',['1' => 'Standard', '2' => 'Combo','3' => 'Digital']);
define('APPROVE_STATUS',['1' => 'Approved', '2' => 'Processing']);
define('EXPENSE_APPROVE_STATUS',['1' => 'Approved', '2' => 'Rejected','3'=>'Pending']);
define('SUPPLIER_TYPE',['1' => 'Material','2' => 'Machine']);
define('TAX_METHOD',['1' => 'Exclusive','2' => 'Inclusive']);
define('STATUS_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Active</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Inactive</span>',
]);
define('PRE_POST_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Pre</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Post</span>',
]);
define('PAID_UNPAID_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Unpaid</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Paid</span>',
]);
define('ALLOWANCE_DEDUCTION_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Allowance</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Deduction</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Others</span>',
]);
define('EXPENSE_APPROVE_STATUS_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Rejected</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Pending</span>',
]);
define('DAY_NIGHT_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Day</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Night</span>',
]);
define('SUPPLIER_TYPE_LABEL',[
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Material</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Machine</span>',
]);

define('APPROVE_STATUS_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Processing</span>',
]);
define('STOCK_STATUS_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Available</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Out of Stock</span>',
]);
define('DELETABLE_LABEL',[
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Yes</span>',
]);
define('BARCODE_SYMBOL',([
    "C128"  => "Code 128",
    "C39"   => "Code 39",
    "UPCA"  => "UPC-A",
    "UPCE"  => "UPC-E",
    "EAN8"  => "EAN-8",
    "EAN13" => "EAN-13"
]));
define('ASSET_STATUS',([
    "1" => "Good",
    "2" => "Broken",
    "3" => "Recycled",
    "4" => "On Service",
    "5" => "Archived",
]));

define('PRODUCTION_STATUS',['1'=>'Pending', '2'=>'Finished']);
define('PRODUCTION_STATUS_LABEL',[
    '1'=>'<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2'=>'<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Finished</span>',
]);

define('PRODUCTION_MATERIAL_DELIVERY_STATUS',['1'=>'Pending','2'=>'Full Delivered','3'=>'Partially Delivered']);
define('PRODUCTION_MATERIAL_DELIVERY_LABEL',[
    '1'=>'<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2'=>'<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Delivered</span>',
    '3'=>'<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Partially Delivered</span>'
]);
define('PRODUCTION_MATERIAL_RECEIVE_STATUS',['1'=>'Pending','2'=>'Received','3'=>'Partially Received']);
define('PRODUCTION_MATERIAL_RECEIVE_LABEL',[
    '1'=>'<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2'=>'<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Received</span>',
    '3'=>'<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Partially Received</span>'
]);

define('DAYS',['1'=>'Saturday','2'=>'Sunday','3'=>'Monday','4'=>'Tuesday','5'=>'Wednesday','6'=>'Thursday']);

define('PURCHASE_STATUS',['1'=>'Received','2'=>'Partial','3'=>'Pending','4'=>'Ordered']);
define('PURCHASE_STATUS_LABEL',[
    '1'=>'<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Received</span>',
    '2'=>'<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Partial</span>',
    '3'=>'<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '4'=>'<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Ordered</span>',
]);

define('PAYMENT_STATUS',['1'=>'Paid','2'=>'Partial','3'=>'Due']);
define('PAYMENT_STATUS_LABEL',[
    '1'=>'<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Paid</span>',
    '2'=>'<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Partial</span>',
    '3'=>'<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Due</span>',
]);
define('PAYMENT_METHOD',['1'=>'Cash','2'=>'Cheque','3'=>'Mobile Bank']);
define('SALE_PAYMENT_METHOD',['1'=>'Cash','2'=>'Bank','3'=>'Mobile Bank']);
define('DELIVERY_STATUS',['1'=>'Pending','2'=>'Partially Delivered','3'=>'Delivered']);
define('MAIL_MAILER',(['smtp','sendmail','mail']));
define('MAIL_ENCRYPTION',(['none' => 'null','tls' => 'tls','ssl' => 'ssl']));

//Employee Form Constant
define('JOB_STATUS',['1'=>'Permanent','2'=>'Probation','3'=>'Resigned','4'=>'Suspended']);
define('DUTY_TYPE',['1'=>'Full Time','2'=>'Part Time','3'=>'Contractual','4'=>'Other']);
define('RATE_TYPE',['1'=>'Hourly','2'=>'Salary']);
define('PAY_FREQUENCY',['1'=>'Weekly','2'=>'Biweekly','3'=>'Monthly','4'=>'Annual']);
define('GENDER',['1'=>'Male','2'=>'Female','3'=>'Other']);
define('MARITAL_STATUS',['1'=>'Single','2'=>'Married','3'=>'Divorced','4'=>'Widowed','5'=>'Other']);
define('BLOOD_GROUP',['1'=>'A+','2'=>'B+','3'=>'A-','4'=>'B-','5'=>'AB+','6'=>'AB-','7'=>'O+','8'=>'O-']);
define('IS_SUPERVISOR',['1'=>'Yes','2'=>'No']);
define('OVERTIME',['1'=>'Allowed','2'=>'Not Allowed']);
define('RESIDENTIAL_STATUS',['1'=>'Resident','2'=>'Non Resident']);

if (!function_exists('permission')) {

    function permission(string $value){
        if (collect(\Illuminate\Support\Facades\Session::get('user_permission'))->contains($value)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('change_status')) {

    function change_status(int $id,int $status,string $name){
        if($id && $status && $name){
            return $status == 1 ? '<span class="label label-success label-pill label-inline change_status" data-id="' . $id . '" data-name="' . $name . '" data-status="2" style="min-width:70px !important;cursor:pointer;">Active</span>'
            : '<span class="label label-danger label-pill label-inline change_status" data-id="' . $id . '" data-name="' . $name . '" data-status="1"  style="min-width:70px !important;cursor:pointer;">Inactive</span>';
        }
    }
}

if (!function_exists('action_button')) {

    function action_button($action){
        return '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-th-list text-white"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $action . '
                    </div>
                </div>';
    }
}

if (!function_exists('row_checkbox')) {

    function row_checkbox($id){
        return '<div class="custom-control custom-checkbox">
                    <input type="checkbox" value="'.$id.'"
                    class="custom-control-input select_data" onchange="select_single_item()" id="checkbox'.$id.'">
                    <label class="custom-control-label" for="checkbox'.$id.'"></label>
                </div>';
    }
}

if(!function_exists('generator'))
{
    function generator($lenth)
    {
        $number=array("A","B","C","D","E","F","G","H","I","J","K","L","N","M","O","P","Q","R","S","U","V","T","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");
    
        for($i=0; $i<$lenth; $i++)
        {
            $rand_value=rand(0,34);
            $rand_number=$number["$rand_value"];
        
            if(empty($con))
            { 
            $con=$rand_number;
            }
            else
            {
            $con="$con"."$rand_number";}
        }
        return $con;
    }
}
if (!function_exists('numberTowords')) {
    function numberTowords($num)
    {

        $ones = array(
            0 => "ZERO",
            1 => "ONE",
            2 => "TWO",
            3 => "THREE",
            4 => "FOUR",
            5 => "FIVE",
            6 => "SIX",
            7 => "SEVEN",
            8 => "EIGHT",
            9 => "NINE",
            10 => "TEN",
            11 => "ELEVEN",
            12 => "TWELVE",
            13 => "THIRTEEN",
            14 => "FOURTEEN",
            15 => "FIFTEEN",
            16 => "SIXTEEN",
            17 => "SEVENTEEN",
            18 => "EIGHTEEN",
            19 => "NINETEEN",
            "014" => "FOURTEEN",
        );
        $tens = array(
            0 => "ZERO",
            1 => "TEN",
            2 => "TWENTY",
            3 => "THIRTY",
            4 => "FORTY",
            5 => "FIFTY",
            6 => "SIXTY",
            7 => "SEVENTY",
            8 => "EIGHTY",
            9 => "NINETY",
        );
        $hundreds = array(
            "HUNDRED",
            "THOUSAND",
            "MILLION",
            "BILLION",
            "TRILLION",
            "QUARDRILLION",
        ); /*limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {

            while (substr($i, 0, 1) == "0") {
                $i = substr($i, 1, 5);
            }

            if ($i < 20) {
/* echo "getting:".$i; */
                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $tens[substr($i, 0, 1)];
                }

                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
                }

            } else {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                }

                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                }

                if (substr($i, 2, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
                }

            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " AND ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt;
    }

}

