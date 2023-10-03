<?php

class Mailer extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        // Load PHPMailer library
        $this->load->library('phpmailer_lib');
    }

    function invoice()
    {
        $this->load->model('system/s_emailschedule');
        $x = $this->s_emailschedule->get_one('INVOICE');

        if (isset($this->sys_input['id']))
            $x = $this->s_emailschedule->get_by('INVOICE', $this->sys_input['id']);
            // echo $this->db->last_query();
        if ($x)
        {
            $id = $x->S_EmailScheduleReffID;
            $this->load->model('sales/l_invoice');
            $r = $this->l_invoice->get($id);

            // PHPMailer object
            $mail = $this->phpmailer_lib->load();

            // SMTP configuration
            $this->load->model('system/s_email');
            $em = $this->s_email->get_rotate();

            $mail->isSMTP();
            $mail->Host     = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $em->email_username;
            $mail->Password = $em->email_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port     = 587;
            
            $mail->setFrom($em->email_username, 'Zalfa Miracle');
            // $mail->addReplyTo('onecode.id@gmail.com', 'CodexWorld');

            // Add a recipient
            $mail->addAddress($x->S_EmailScheduleAddress);
            
            // Email subject
            $mail->Subject = 'Customer Invoice untuk '.$r->M_CustomerName;
            
            // Set email format to HTML
            $mail->isHTML(true);
            
            // Email body content
            // $mailContent = "<h1>Send HTML Email using SMTP in CodeIgniter</h1>
            //     <p>This is a test email sending using SMTP mail server with PHPMailer.</p>";
            $mailContent = $this->load->view('mail/invoice', (array)$r, true);
            $mail->Body = $mailContent;
            
            // Attachment
            $mail->addAttachment('/home/one/Web/uploads/invoices/'.$r->L_SoNumber.'.pdf');

            // Send email
            if(!$mail->send()){
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }else{
                echo 'Email telah terkirim ke '.$x->S_EmailScheduleAddress;
                
                $this->s_emailschedule->sent($x->S_EmailScheduleID);
            }
        }
    }

    function send_the_unsent()
    {
        $this->load->model('system/s_emailschedule');
        $x = $this->s_emailschedule->get_unsent('2020-01-01', 2);

        if ($x)
        {
            $this->load->model('sales/l_invoice');
            $this->load->model('sales/c_invoice');
            $this->load->model('system/s_email');
            $this->load->model('master/m_bankaccount');
            $this->load->model('master/m_customeraccount');
            $this->load->model('finance/f_ipaymu');

            foreach ($x as $k => $v)
            {
                $id = $v['S_EmailScheduleReffID'];

                // PHPMailer object
                $mail = $this->phpmailer_lib->load();

                // SMTP configuration
                $em = $this->s_email->get_rotate();
                $mail->isSMTP();
                $mail->Host     = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $em->email_username;
                $mail->Password = $em->email_password;
                $mail->SMTPSecure = 'tls';
                $mail->Port     = 587;
                
                $mail->setFrom($em->email_username, 'Zalfa Miracle');
                // $mail->addReplyTo('onecode.id@gmail.com', 'CodexWorld');

                // Add a recipient
                $mail->addAddress($v['S_EmailScheduleAddress']);
                
                // Email subject
                $mail->Subject = $v['S_EmailScheduleSubject'];
                
                // Set email format to HTML
                $mail->isHTML(true);
                
                // Email body content
                // $mailContent = "<h1>Send HTML Email using SMTP in CodeIgniter</h1>
                //     <p>This is a test email sending using SMTP mail server with PHPMailer.</p>";
                $mailContent = $v['S_EmailScheduleContent'];
                if ($v['S_EmailScheduleType'] == 'INVOICE')
                {
                    $r = $this->l_invoice->get($id);
                    $r->account = $this->m_bankaccount->search(['account_name'=>'%','page'=>1])['records'];
                    $r->ipaymu = $this->f_ipaymu->get($r->L_InvoiceL_SoID);

                    $this->load->model('sales/l_so');
                    $so = $this->l_so->get_one($r->L_SoID);
                    $r = (array)$r;
                    if ($so->payment_code == 'IPAYMU.QRIS')
                    {
                        $this->load->model('finance/f_ipaymu');
                        $ipaymu = $this->f_ipaymu->get($r['L_SoID']);
                        
                        // https://my.ipaymu.com/qr/1425218
                        // $qr = file_get_contents("https://my.ipaymu.com/qr/".$ipaymu->F_IpaymuTrxID);
                        $r['qrimage'] = "https://my.ipaymu.com/qr/".$ipaymu->F_IpaymuTrxID;
                    }

                    $mailContent = $this->load->view('mail/invoice', $r, true);
                    // Attachment
                    $mail->addAttachment('/home/one/Web/uploads/invoices/'.$r['L_SoNumber'].'.pdf');
                }

                if ($v['S_EmailScheduleType'] == 'CO-INVOICE')
                {
                    $r = $this->c_invoice->get($id);
                    $r->account = $this->m_customeraccount->search(['account_name'=>'%','page'=>1,'customer_id'=>$r->C_SoToM_CustomerID])['records'];
                    $r = (array)$r;

                    $mailContent = $this->load->view('mail/co_invoice', $r, true);
                    // Attachment
                    $mail->addAttachment('/home/one/Web/uploads/invoices/'.$r['C_SoNumber'].'.pdf');
                }
                    
                $mail->Body = $mailContent;
                // Send email
                if(!$mail->send()){
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                }else{
                    echo 'Email telah terkirim ke '.$v['S_EmailScheduleAddress'];
                    
                    $this->s_emailschedule->sent($v['S_EmailScheduleID']);
                }
            }
            
        }
    }

    function index()
    {
        $id = $this->sys_input['id'];
        $this->load->model('sales/l_invoice');
        $r = $this->l_invoice->get($id);

        // PHPMailer object
        $mail = $this->phpmailer_lib->load();

        // SMTP configuration
        $this->load->model('system/s_email');
        $em = $this->s_email->get_rotate();

        $mail->isSMTP();
        $mail->Host     = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $em->email_username;
        $mail->Password = $em->email_password;
        $mail->SMTPSecure = 'tls';
        $mail->Port     = 587;
        
        $mail->setFrom('onecode.id@gmail.com', 'Zalfa Miracle');
        // $mail->addReplyTo('onecode.id@gmail.com', 'CodexWorld');

        // Add a recipient
        $mail->addAddress('heriabunizar@gmail.com');
        
        // Email subject
        $mail->Subject = 'Customer Invoice untuk '.$r->M_CustomerName;
        
        // Set email format to HTML
        $mail->isHTML(true);
        
        // Email body content
        // $mailContent = "<h1>Send HTML Email using SMTP in CodeIgniter</h1>
        //     <p>This is a test email sending using SMTP mail server with PHPMailer.</p>";

        $this->load->model('sales/l_so');
        $so = $this->l_so->get_one($r->L_SoID);
        $r = (array)$r;
        if ($so->payment_code == 'IPAYMU.QRIS')
        {
            $this->load->model('finance/f_ipaymu');
            $ipaymu = $this->f_ipaymu->get($r['L_SoID']);
            
            // https://my.ipaymu.com/qr/1425218
            $qr = file_get_contents("https://my.ipaymu.com/qr/".$ipaymu->F_IpaymuTrxID);
            $r['qrimage'] = "https://my.ipaymu.com/qr/".$ipaymu->F_IpaymuTrxID;
        }

        $mailContent = $this->load->view('mail/invoice', $r, true);
        $mail->Body = $mailContent;
        
        // Attachment
        $mail->addAttachment('/home/one/Web/uploads/invoices/'.$r->L_SoNumber.'.pdf');

        // Send email
        if(!$mail->send()){
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }else{
            echo 'Message has been sent';
        }
    }

    function test($id)
    {
        $this->load->model('sales/l_invoice');
        $this->load->model('master/m_bankaccount');
        $this->load->model('finance/f_ipaymu');
        $r = $this->l_invoice->get($id);
        $r->ipaymu = $this->f_ipaymu->get($r->L_InvoiceL_SoID);
        $r->account = $this->m_bankaccount->search(['account_name'=>'%','page'=>1])['records'];
        
        $this->load->view('mail/invoice', (array)$r);
    }

    function test_user()
    {        
        $this->load->view('mail/usercustomer', null);
    }
}
?>