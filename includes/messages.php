<?php






function setMessage($type, $message)
{
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}



function getMessage()
{
    if (!isset($_SESSION)) {
        session_start();
    }

    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}



function displayMessage()
{
    $message = getMessage();
    if ($message) {
        $typeClass = '';
        $icon = '';

        switch ($message['type']) {
            case 'success':
                $typeClass = 'alert-success';
                $icon = 'fa-check-circle';
                break;
            case 'error':
                $typeClass = 'alert-danger';
                $icon = 'fa-times-circle';
                break;
            case 'warning':
                $typeClass = 'alert-warning';
                $icon = 'fa-exclamation-triangle';
                break;
            case 'info':
                $typeClass = 'alert-info';
                $icon = 'fa-info-circle';
                break;
            default:
                $typeClass = 'alert-info';
                $icon = 'fa-info-circle';
        }

        $customStyle = '';
        if ($message['type'] === 'success') {
            $customStyle = 'background: linear-gradient(135deg, rgba(20, 184, 166, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%); border: 2px solid #14b8a6; color: #0e7490;';
        }

        echo '<div class="alert ' . $typeClass . ' alert-dismissible fade show" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); opacity: 1 !important; ' . $customStyle . '">
                <i class="fas ' . $icon . '"></i> ' . htmlspecialchars($message['message']) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <script>
                setTimeout(function() {
                    $(".alert").fadeOut("slow");
                }, 5000);
              </script>';
    }
}



function redirectWithMessage($url, $type, $message)
{
    setMessage($type, $message);
    header("Location: " . $url);
    exit();
}
