<?php
require_once 'db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $flag = $_POST['flag'];
    if ($flag == 1) {
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        // Create your SQL query to fetch the data based on the date range
        $query = "SELECT cd.invoice_date, cd.invoice_no, cb.total_sgst, cb.total_cgst, cb.net_amount, ii.serial_no 
        FROM (
            SELECT ci.customer_id, MAX(ii.serial_no) AS max_serial 
            FROM invoice_items AS ii 
            INNER JOIN customer_details AS ci ON ci.customer_id = ii.customer_id 
            WHERE STR_TO_DATE(ci.invoice_date, '%d/%m/%Y') >= STR_TO_DATE('$from_date', '%d/%m/%Y') 
              AND STR_TO_DATE(ci.invoice_date, '%d/%m/%Y') < STR_TO_DATE('$to_date', '%d/%m/%Y') + INTERVAL 1 DAY
            GROUP BY ci.customer_id
        ) AS m 
        JOIN invoice_items AS ii ON ii.customer_id = m.customer_id AND ii.serial_no = m.max_serial 
        INNER JOIN customer_details AS cd ON cd.customer_id = ii.customer_id 
        INNER JOIN customer_bill AS cb ON cb.customer_id = ii.customer_id 
        ORDER BY cd.invoice_no ASC";

        $result = $con->query($query);

        if ($result->num_rows > 0) {
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data); // Send the data as JSON
        } else {
            echo json_encode([]); // Send an empty JSON array if no data found
        }
    } else if ($flag == 2) {

        $invoice_no = $_POST['invoice_no'];

        $customer_details = "SELECT * FROM customer_details WHERE invoice_no = '$invoice_no'";
        $result4 = $con->query($customer_details);
        if ($result4) {
            if ($result4->num_rows > 0) {
                $row3 = mysqli_fetch_assoc($result4);

                $customer_id = $row3["customer_id"];
                $invoice_no = $row3["invoice_no"];
                $invoice_date = $row3["invoice_date"];
                $customer_name = $row3["customer_name"];
                $mobile = $row3["mobile"];
                $address = $row3["address"];
                $customer_gstin = $row3["customer_gstin"];
                $place_of_supply = $row3["place_of_supply"];
                $city = $row3["city"];
                $state = $row3["state"];
            }
        }

        $customer_bill = "SELECT * FROM customer_bill WHERE customer_id = '$customer_id'";
        $result5 = $con->query($customer_bill);
        if ($result5) {
            if ($result5->num_rows > 0) {
                $row4 = mysqli_fetch_assoc($result5);

                $total_discount = $row4["total_discount"];
                $total_value = $row4["total_value"];
                $total_cgst = $row4["total_cgst"];
                $total_sgst = $row4["total_sgst"];
                $net_amount = $row4["net_amount"];
            }
        }

        $sql2="SELECT customer_id FROM customer_details WHERE invoice_no = '$invoice_no'";
        $result2=mysqli_query($con,$sql2);

        if($result2)
        {
            if($result2->num_rows > 0)
            {
                $row = mysqli_fetch_assoc($result2);
                $customer_id = $row['customer_id'];
            }
        }

        $invoice_items = "SELECT * FROM invoice_items WHERE customer_id = '$customer_id'";
        $result6 = $con->query($invoice_items);

        if ($result6->num_rows > 0) {
            $data = array();
            while ($row = $result6->fetch_assoc()) {
                $data[] = $row;
            }
        }

        print(json_encode(array('status' => true, 'invoice_no' => $invoice_no, 'invoice_date' => $invoice_date, 'customer_name' => $customer_name, 'mobile' => $mobile, 'address' => $address, 'customer_gstin' => $customer_gstin, 'place_of_supply' => $place_of_supply, 'city' => $city, 'state' => $state, 'total_discount' => $total_discount, 'total_value' => $total_value, 'total_cgst' => $total_cgst, 'total_sgst' => $total_sgst, 'net_amount' => $net_amount, 'items_data' => $data)));
    }
}

$con->close();
?>