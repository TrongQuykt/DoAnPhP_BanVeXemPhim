<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Thiết Kế Lại</title>

    <style>
        /* Thiết kế footer */
        footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
            color: #fff;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        .social-media-wrapper ul
        {
            list-style: none;
        }
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ff00ff, #00ffff);
        }

        .footer-section {
            margin-bottom: 30px;
        }

        .footer-section h4 {
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #00ffff;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
            margin-bottom: 20px;
            position: relative;
        }

        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 2px;
            background: #ff00ff;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .footer-section ul li a:hover {
            color: #00ffff;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
            transform: translateX(5px);
        }

        .footer-section ul li i {
            margin-right: 10px;
            font-size: 1.1rem;
            color: #ff00ff;
        }

        .footer-section ul li a:hover i {
            color: #00ffff;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 40px;
        }

        .footer-bottom .copyright {
            font-size: 0.9rem;
            color: #999;
            text-align: center;
        }

        .social-media-wrapper ul {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .social-media-wrapper ul li a {
            font-size: 1.5rem;
            color: #fff;
            background: linear-gradient(45deg, #ff00ff, #00ffff);
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        .social-media-wrapper ul li a:hover {
            transform: scale(1.2);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-section {
                text-align: center;
            }

            .footer-section h4::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .social-media-wrapper ul {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <footer>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-10 col-md-8">
                    <div class="row gy-4 gy-md-0">
                        <!-- Giới thiệu -->
                        <div class="col-6 col-md-3">
                            <div class="footer-section">
                                <h4>Giới thiệu</h4>
                                <ul>
                                    <li>
                                        <a href="#!"><i class="bi bi-info-circle"></i> Về chúng tôi</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-file-earmark-text"></i> Thỏa thuận sử dụng</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-shield-check"></i> Quy chế hoạt động</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-lock"></i> Chính sách bảo mật</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Talent -->
                        <div class="col-6 col-md-3">
                            <div class="footer-section">
                                <h4>Talent</h4>
                                <ul>
                                    <li>
                                        <a href="#!"><i class="bi bi-gear"></i> Operations</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-megaphone"></i> Marketing</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-currency-dollar"></i> Finance</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-box"></i> Product</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-headset"></i> Support</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Resources -->
                        <div class="col-6 col-md-3">
                            <div class="footer-section">
                                <h4>Resources</h4>
                                <ul>
                                    <li>
                                        <a href="#!"><i class="bi bi-people"></i> Community</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-book"></i> Resources</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-mortarboard"></i> Learning</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-camera-video"></i> Webinars</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-person-heart"></i> Customers</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Company -->
                        <div class="col-6 col-md-3">
                            <div class="footer-section">
                                <h4>Company</h4>
                                <ul>
                                    <li>
                                        <a href="#!"><i class="bi bi-building"></i> About us</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-person-gear"></i> Leadership</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-briefcase"></i> Careers</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-tools"></i> Services</a>
                                    </li>
                                    <li>
                                        <a href="#!"><i class="bi bi-telephone"></i> Contact Us</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Footer Bottom -->
                    <div class="footer-bottom">
                        <div class="row gy-3 align-items-center">
                            <div class="col-12 col-md-6">
                                <div class="copyright">
                                    © 2025.Copy Right By Trong Quy.
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="social-media-wrapper">
                                    <ul>
                                        <li>
                                            <a href="#" title="YouTube"><i class="bi bi-youtube"></i></a>
                                        </li>
                                        <li>
                                            <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
                                        </li>
                                        <li>
                                            <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                                        </li>
                                        <li>
                                            <a href="#" title="GitHub"><i class="bi bi-github"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <!-- Scrollbar (nếu cần) -->
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>