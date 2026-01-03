<?php
echo "<script>
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        // Jika tidak ada token, lempar ke login
        window.location.href = 'views/auth/login.html';
    } else {
        // Jika ada token, lempar ke dashboard
        window.location.href = 'views/dashboard/index.html';
    }
</script>";
?>