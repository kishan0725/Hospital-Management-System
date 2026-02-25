<!DOCTYPE html>
<html lang="vi">
<?php
session_start();
$base_path = '../';
require_once('../config.php');

function getDoctors($pdo)
{
    $stmt = $pdo->prepare("
        SELECT d.*, s.name_vi as spec_name 
        FROM doctb d 
        LEFT JOIN specializations s ON d.spec_id = s.id 
        WHERE d.status = 1
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getSpecializations($pdo)
{
    
    
    $stmt = $pdo->prepare("
        SELECT s.*, 
               s.average_rating,
               s.total_ratings,
               (SELECT COUNT(*) FROM doctb WHERE spec_id = s.id AND status = 1) as doc_count
        FROM specializations s 
        WHERE s.status = 1
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$doctors = getDoctors($pdo);
$specializations = getSpecializations($pdo);
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Đánh giá Bác sĩ & Dịch vụ - Global Hospitals</title>

    <link rel="shortcut icon" href="../images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #d2302c;
            --secondary: #ff4d4d;
            --accent: #ffd700;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-image:
                linear-gradient(135deg, rgba(243, 244, 246, 0.9) 0%, rgba(229, 231, 235, 0.9) 100%),
                url('../images/ngua.png');
            background-size: cover, contain;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            background-attachment: fixed, fixed;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(210, 48, 44, 0.85) 0%, rgba(255, 77, 77, 0.85) 100%), url('../images/navbar.png') center/cover no-repeat;
            color: white;
            padding: 3rem 0;
            margin-top: 70px;
            /* Added to clear fixed navbar */
            margin-bottom: 1rem;
            text-align: center;
            position: relative;
        }

        .nav-pills .nav-link {
            color: #4b5563;
            background-color: white;
            margin: 0 0.5rem;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(210, 48, 44, 0.3);
        }

        .card-item {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .avatar-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #e5e7eb;
        }

        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rating-stars {
            color: var(--accent);
            font-size: 0.9rem;
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: #fff5f5;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .rating-badge {
            background: #fffbeb;
            color: #b45309;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            /* Override text-truncate */
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 2rem 1rem;
            }

            .card-item {
                padding: 1.25rem !important;
            }

            .service-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .filter-section .form-control,
        .filter-section .custom-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .filter-section .form-control:focus,
        .filter-section .custom-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(210, 48, 44, 0.1);
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .reset-filter-btn {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .reset-filter-btn:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .no-results i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    
    <?php include('../includes/navbar.php'); ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="font-weight-bold">Đánh giá chất lượng</h1>
            <p class="lead mb-0">Xem đánh giá về đội ngũ bác sĩ và dịch vụ của chúng tôi</p>
        </div>
    </div>

    <div class="container pb-5">
        
        <ul class="nav nav-pills justify-content-center mb-5" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-doctors-tab" data-toggle="pill" href="#pills-doctors" role="tab">
                    <i class="fas fa-user-md mr-2"></i>Bác sĩ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-services-tab" data-toggle="pill" href="#pills-services" role="tab">
                    <i class="fas fa-briefcase-medical mr-2"></i>Dịch vụ
                </a>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-doctors" role="tabpanel">
                
                <div class="filter-section">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="filter-label">Tìm kiếm bác sĩ</label>
                            <input type="text" id="searchDoctor" class="form-control" placeholder="Nhập tên bác sĩ...">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Chuyên khoa</label>
                            <select id="filterSpecialization" class="custom-select">
                                <option value="">Tất cả chuyên khoa</option>
                                <?php
                                $specs = array_unique(array_column($doctors, 'spec_name'));
                                sort($specs);
                                foreach ($specs as $spec):
                                    if (!empty($spec)):
                                ?>
                                        <option value="<?php echo htmlspecialchars($spec); ?>"><?php echo htmlspecialchars($spec); ?></option>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Sắp xếp</label>
                            <select id="sortDoctor" class="custom-select">
                                <option value="rating-desc">Đánh giá cao nhất</option>
                                <option value="rating-asc">Đánh giá thấp nhất</option>
                                <option value="name-asc">Tên A-Z</option>
                                <option value="name-desc">Tên Z-A</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-right">
                            <a href="#" id="resetDoctorFilter" class="reset-filter-btn">
                                <i class="fas fa-redo-alt mr-1"></i>Đặt lại
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row" id="doctorsList">
                    <?php foreach ($doctors as $doc): ?>
                        <div class="col-12 col-sm-6 col-lg-3 mb-4 doctor-item"
                            data-name="<?php echo strtolower(htmlspecialchars($doc['fullname'])); ?>"
                            data-spec="<?php echo htmlspecialchars($doc['spec_name']); ?>"
                            data-rating="<?php echo $doc['average_rating'] ?? 0; ?>">
                            <div class="card-item p-4 text-center">
                                <div class="avatar-wrapper">
                                    <?php
                                    $avatar = !empty($doc['avatar']) ? $doc['avatar'] : 'images/user.png';
                                    if (strpos($avatar, 'http') !== 0) {
                                        $avatar = '../' . $avatar;
                                    }
                                    ?>
                                    <img src="<?php echo $avatar; ?>"
                                        alt="<?php echo htmlspecialchars($doc['fullname']); ?>"
                                        onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($doc['fullname']); ?>&background=0e7490&color=fff'">
                                </div>
                                <h5 class="font-weight-bold mb-1"><?php echo htmlspecialchars($doc['fullname']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($doc['spec_name']); ?></p>

                                <div class="mb-3">
                                    <div class="rating-badge">
                                        <i class="fas fa-star text-warning"></i>
                                        <span><?php echo number_format($doc['average_rating'] ?? 0, 1); ?></span>
                                        <span class="text-muted font-weight-normal ml-1">(<?php echo $doc['total_ratings']; ?>)</span>
                                    </div>
                                </div>

                                <a href="doctor_details.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-info btn-sm btn-block rounded-pill">
                                    Xem & Đánh giá
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                
                <div id="noDoctorsFound" class="no-results" style="display: none;">
                    <i class="fas fa-user-md"></i>
                    <h4>Không tìm thấy bác sĩ</h4>
                    <p>Vui lòng thử lại với bộ lọc khác</p>
                </div>
            </div>

            
            <div class="tab-pane fade" id="pills-services" role="tabpanel">
                
                <div class="filter-section">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="filter-label">Tìm kiếm dịch vụ</label>
                            <input type="text" id="searchService" class="form-control" placeholder="Nhập tên dịch vụ...">
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="filter-label">Sắp xếp</label>
                            <select id="sortService" class="custom-select">
                                <option value="rating-desc">Đánh giá cao nhất</option>
                                <option value="rating-asc">Đánh giá thấp nhất</option>
                                <option value="name-asc">Tên A-Z</option>
                                <option value="name-desc">Tên Z-A</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="filter-label">Số bác sĩ</label>
                            <select id="filterDoctorCount" class="custom-select">
                                <option value="">Tất cả</option>
                                <option value="5+">5+ bác sĩ</option>
                                <option value="10+">10+ bác sĩ</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-right">
                            <a href="#" id="resetServiceFilter" class="reset-filter-btn">
                                <i class="fas fa-redo-alt mr-1"></i>Đặt lại
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row" id="servicesList">
                    <?php foreach ($specializations as $spec): ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-4 service-item"
                            data-name="<?php echo strtolower(htmlspecialchars($spec['name_vi'])); ?>"
                            data-rating="<?php echo $spec['average_rating'] ?? 0; ?>"
                            data-doccount="<?php echo $spec['doc_count'] ?? 0; ?>">
                            <div class="card-item p-4">
                                <div class="d-flex align-items-start h-100">
                                    <div class="service-icon flex-shrink-0">
                                        <i class="<?php echo !empty($spec['icon']) ? $spec['icon'] : 'fas fa-stethoscope'; ?>"></i>
                                    </div>
                                    <div class="ml-3 flex-grow-1 d-flex flex-column">
                                        <h5 class="font-weight-bold"><?php echo htmlspecialchars($spec['name_vi']); ?></h5>

                                        
                                        <p class="text-muted small mb-2 text-clamp-2" title="<?php echo htmlspecialchars($spec['description'] ?? $spec['name']); ?>">
                                            <?php echo htmlspecialchars($spec['description'] ?? $spec['name']); ?>
                                        </p>

                                        <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
                                            <div class="rating-badge">
                                                <i class="fas fa-star text-warning"></i>
                                                <span><?php echo number_format($spec['average_rating'] ?? 0, 1); ?></span>
                                                <span class="text-muted font-weight-normal ml-1">(<?php echo $spec['total_ratings'] ?? 0; ?>)</span>
                                            </div>
                                            <a href="service_details.php?id=<?php echo $spec['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill">
                                                Xem & Đánh giá
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                
                <div id="noServicesFound" class="no-results" style="display: none;">
                    <i class="fas fa-briefcase-medical"></i>
                    <h4>Không tìm thấy dịch vụ</h4>
                    <p>Vui lòng thử lại với bộ lọc khác</p>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // ===== DOCTOR FILTERS =====
            function filterDoctors() {
                const searchTerm = $('#searchDoctor').val().toLowerCase();
                const specFilter = $('#filterSpecialization').val();
                const sortBy = $('#sortDoctor').val();

                let doctors = $('.doctor-item').get();
                let visibleCount = 0;

                // Filter
                doctors.forEach(function(doctor) {
                    const $doctor = $(doctor);
                    const name = $doctor.data('name');
                    const spec = $doctor.data('spec');

                    const matchSearch = name.includes(searchTerm);
                    const matchSpec = !specFilter || spec === specFilter;

                    if (matchSearch && matchSpec) {
                        $doctor.show();
                        visibleCount++;
                    } else {
                        $doctor.hide();
                    }
                });

                // Sort visible doctors
                doctors.sort(function(a, b) {
                    const $a = $(a);
                    const $b = $(b);

                    if (sortBy === 'rating-desc') {
                        return parseFloat($b.data('rating')) - parseFloat($a.data('rating'));
                    } else if (sortBy === 'rating-asc') {
                        return parseFloat($a.data('rating')) - parseFloat($b.data('rating'));
                    } else if (sortBy === 'name-asc') {
                        return $a.data('name').localeCompare($b.data('name'));
                    } else if (sortBy === 'name-desc') {
                        return $b.data('name').localeCompare($a.data('name'));
                    }
                    return 0;
                });

                // Re-append sorted doctors
                $('#doctorsList').append(doctors);

                // Show/hide no results message
                if (visibleCount === 0) {
                    $('#noDoctorsFound').show();
                } else {
                    $('#noDoctorsFound').hide();
                }
            }

            $('#searchDoctor, #filterSpecialization, #sortDoctor').on('change keyup', filterDoctors);

            $('#resetDoctorFilter').click(function(e) {
                e.preventDefault();
                $('#searchDoctor').val('');
                $('#filterSpecialization').val('');
                $('#sortDoctor').val('rating-desc');
                filterDoctors();
            });

            // ===== SERVICE FILTERS =====
            function filterServices() {
                const searchTerm = $('#searchService').val().toLowerCase();
                const sortBy = $('#sortService').val();
                const docCountFilter = $('#filterDoctorCount').val();

                let services = $('.service-item').get();
                let visibleCount = 0;

                // Filter
                services.forEach(function(service) {
                    const $service = $(service);
                    const name = $service.data('name');
                    const docCount = parseInt($service.data('doccount'));

                    const matchSearch = name.includes(searchTerm);
                    let matchDocCount = true;

                    if (docCountFilter === '5+') {
                        matchDocCount = docCount >= 5;
                    } else if (docCountFilter === '10+') {
                        matchDocCount = docCount >= 10;
                    }

                    if (matchSearch && matchDocCount) {
                        $service.show();
                        visibleCount++;
                    } else {
                        $service.hide();
                    }
                });

                // Sort visible services
                services.sort(function(a, b) {
                    const $a = $(a);
                    const $b = $(b);

                    if (sortBy === 'rating-desc') {
                        return parseFloat($b.data('rating')) - parseFloat($a.data('rating'));
                    } else if (sortBy === 'rating-asc') {
                        return parseFloat($a.data('rating')) - parseFloat($b.data('rating'));
                    } else if (sortBy === 'name-asc') {
                        return $a.data('name').localeCompare($b.data('name'));
                    } else if (sortBy === 'name-desc') {
                        return $b.data('name').localeCompare($a.data('name'));
                    }
                    return 0;
                });

                // Re-append sorted services
                $('#servicesList').append(services);

                // Show/hide no results message
                if (visibleCount === 0) {
                    $('#noServicesFound').show();
                } else {
                    $('#noServicesFound').hide();
                }
            }

            $('#searchService, #sortService, #filterDoctorCount').on('change keyup', filterServices);

            $('#resetServiceFilter').click(function(e) {
                e.preventDefault();
                $('#searchService').val('');
                $('#sortService').val('rating-desc');
                $('#filterDoctorCount').val('');
                filterServices();
            });
        });
    </script>

    
    <script type="text/javascript">
        (function() {
            const isMobile = window.matchMedia('(max-width: 767px)').matches;
            const petalCount = isMobile ? 10 : 20;
            const petalImage = 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEizrrtX-KQtKY8e8pxCHjLROT5pYW7sVkUpET9HHpW8QO-PnoIRKVsvRDxM6shrE4Q-44Oh9teSGK1SApaZ1OJvhR4z7ENgKSJOLWfsdKw9jPszAa2HqaE6W8ohyGHRvff6TgKXEUjnn73LLLp3FHbtMTJnIkPxPhujWwG5ZsFgW7ctQ0zrR5KKSqlewg/s16000/hoadao-anonyviet.com.png';

            const petals = [];
            let docWidth = window.innerWidth - 10;
            let docHeight = window.innerHeight;

            // Khởi tạo hoa đào
            for (let i = 0; i < petalCount; i++) {
                const petal = {
                    x: Math.random() * docWidth,
                    y: Math.random() * docHeight,
                    dx: 0,
                    amplitude: Math.random() * 20,
                    speedX: 0.02 + Math.random() / 10,
                    speedY: 0.7 + Math.random(),
                    element: null
                };

                const div = document.createElement('div');
                div.id = 'petal' + i;
                div.style.cssText = `position:fixed;z-index:${99+i};visibility:visible;pointer-events:none;width:15px;left:${petal.x}px;top:${petal.y}px`;
                div.innerHTML = `<img src="${petalImage}" alt="Hoa đào" style="width:100%;height:auto">`;
                document.body.appendChild(div);
                petal.element = div;
                petals.push(petal);
            }

            // Animation loop
            function animate() {
                docWidth = window.innerWidth - 10;
                docHeight = window.innerHeight;

                petals.forEach(petal => {
                    petal.y += petal.speedY;
                    if (petal.y > docHeight - 50) {
                        petal.x = Math.random() * (docWidth - petal.amplitude - 30);
                        petal.y = 0;
                        petal.speedX = 0.02 + Math.random() / 10;
                        petal.speedY = 0.7 + Math.random();
                    }
                    petal.dx += petal.speedX;
                    petal.element.style.top = petal.y + 'px';
                    petal.element.style.left = (petal.x + petal.amplitude * Math.sin(petal.dx)) + 'px';
                });

                requestAnimationFrame(animate);
            }

            animate();
        })();
    </script>

    
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-hospital"></i> Global Hospitals
                    </div>
                    <p>Hệ thống quản lý bệnh viện hiện đại, chuyên nghiệp và tiện lợi.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="footer-links">
                        <li><a href="../index.php">Trang chủ</a></li>
                        <li><a href="reviews.php">Đánh giá</a></li>
                        <li><a href="contact.php">Liên hệ</a></li>
                        <li><a href="forum/index.php">Diễn đàn</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Liên hệ</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> stu735105020@hnue.edu.vn</li>
                        <li><i class="fas fa-phone"></i> (84) 123-456-789</li>
                        <li><i class="fas fa-map-marker-alt"></i> 136 Xuân Thuỷ, Trường Đại học Sư phạm Hà Nội, Việt Nam</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; Nhóm 17 - Hệ thống đặt lịch khám. All rights reserved.</p>
            </div>
        </div>
    </footer>

    
    <div class="medical-lantern-container left-side">
        <div class="medical-string"></div>
        <div class="medical-lantern">
            <div class="medical-lantern-top"></div>
            <div class="medical-lantern-body">
                <span class="medical-lantern-text">Xuân</span>
            </div>
            <div class="medical-lantern-bottom"></div>
            <div class="medical-tassels">
                <span></span><span></span><span></span>
            </div>
            <div class="medical-scroll">
                <div class="medical-scroll-text">
                    <span>Chúc</span>
                    <span>Tết</span>
                    <span>Đến</span>
                    <span>Trăm</span>
                    <span>Điều</span>
                    <span>Như</span>
                    <span>Ý</span>
                </div>
            </div>
        </div>
    </div>

    <div class="medical-lantern-container right-side">
        <div class="medical-string"></div>
        <div class="medical-lantern">
            <div class="medical-lantern-top"></div>
            <div class="medical-lantern-body">
                <span class="medical-lantern-text">2026</span>
            </div>
            <div class="medical-lantern-bottom"></div>
            <div class="medical-tassels">
                <span></span><span></span><span></span>
            </div>
            <div class="medical-scroll">
                <div class="medical-scroll-text">
                    <span>Mừng</span>
                    <span>Xuân</span>
                    <span>Sang</span>
                    <span>Vạn</span>
                    <span>Sự</span>
                    <span>Thành</span>
                    <span>Công</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Mea+Culpa&display=swap');

        .footer {
            background: linear-gradient(135deg, #d2302c 0%, #8b0000 50%, #d2302c 100%);
            color: #ffffff;
            padding: 3rem 0 1rem;
            position: relative;
            overflow: hidden;
            margin-top: 5rem;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700 0%, #d4af37 50%, #ffd700 100%);
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.5);
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffd700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #ffe6e6;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .footer-links a:hover {
            color: #ffd700;
            text-shadow: 0 0 8px rgba(255, 215, 0, 0.6);
            transform: translateX(5px);
            display: inline-block;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 2px solid rgba(255, 215, 0, 0.3);
            color: #ffe6e6;
            font-weight: 500;
        }

        .medical-lantern-container {
            position: fixed;
            top: 0;
            z-index: 9999;
            pointer-events: none;
            transform-origin: top center;
            animation: medical-swing 4s ease-in-out infinite alternate;
        }

        .left-side {
            left: 40px
        }

        .right-side {
            right: 40px;
            animation-delay: 1s
        }

        .medical-string {
            width: 2px;
            height: 70px;
            margin: 0 auto;
            background: linear-gradient(to bottom, #cfc09f, #b8860b);
            position: relative;
        }

        .medical-string::before {
            content: '';
            position: absolute;
            top: -6px;
            left: -4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(circle, #ffd700, #b8860b);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .35), inset 0 1px 1px rgba(255, 255, 255, .6);
        }

        .medical-lantern {
            position: relative;
            pointer-events: auto;
        }

        .medical-lantern-body {
            width: 120px;
            height: 100px;
            border-radius: 35px;
            background: radial-gradient(circle at 30% 30%, #ff5a5a, #7a0000);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 0 12px rgba(255, 200, 120, .35),
                0 0 10px rgba(255, 140, 60, .25),
                0 0 20px rgba(255, 120, 40, .15);
            animation: medical-glow 3s ease-in-out infinite;
        }

        .medical-lantern-text {
            font-family: 'Mr Dafoe', cursive;
            font-size: 36px;
            color: #ffd700;
            text-shadow: 0 0 4px rgba(255, 220, 150, .8), 0 0 8px rgba(255, 180, 100, .5);
        }

        .medical-lantern-top,
        .medical-lantern-bottom {
            width: 60px;
            height: 12px;
            margin: 0 auto;
            background: linear-gradient(90deg, #b8860b, #ffd700, #b8860b);
        }

        .medical-lantern-top {
            margin-bottom: -5px
        }

        .medical-lantern-bottom {
            margin-top: -5px
        }

        .medical-tassels {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            transition: opacity .25s ease;
        }

        .medical-tassels span {
            display: inline-block;
            width: 4px;
            height: 50px;
            margin: 0 2px;
            background: linear-gradient(to bottom, #d2302c, #ff4d4d);
            border-radius: 0 0 5px 5px;
            animation: medical-tassel-sway 2s ease-in-out infinite alternate;
        }

        .medical-tassels span:nth-child(2) {
            height: 65px
        }

        .medical-scroll {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            opacity: 0;
            background:
                linear-gradient(to right, rgba(255, 255, 255, .06), rgba(0, 0, 0, .15), rgba(255, 255, 255, .06)),
                #8b0000;
            border: 2px solid #d4af37;
            box-shadow:
                0 0 0 3px #8b0000,
                0 0 0 5px #d4af37,
                0 6px 12px rgba(0, 0, 0, .4);
            border-radius: 2px;
            overflow: visible;
            transition: width .45s ease, opacity .25s ease;
        }

        .medical-scroll::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 10px;
            background: #ffd700;
        }

        .medical-scroll::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: -12px;
            right: -12px;
            height: 14px;
            background: linear-gradient(to right, #8a6e2f, #ffd700, #8a6e2f);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .45);
        }

        .medical-scroll-text {
            font-family: 'Mea Culpa', cursive;
            font-size: 26px;
            color: #ffd700;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 16px 8px 20px;
            line-height: 1.15;
            text-shadow: 0 1px 1px rgba(0, 0, 0, .6), 0 0 6px rgba(255, 215, 0, .4);
        }

        .medical-lantern:hover .medical-tassels {
            opacity: 0
        }

        .medical-lantern:hover .medical-scroll {
            width: 82px;
            opacity: 1
        }

        @keyframes medical-swing {
            from {
                transform: rotate(-3deg)
            }

            to {
                transform: rotate(3deg)
            }
        }

        @keyframes medical-tassel-sway {
            from {
                transform: rotate(2deg)
            }

            to {
                transform: rotate(-2deg)
            }
        }

        @keyframes medical-glow {
            0% {
                filter: brightness(1)
            }

            50% {
                filter: brightness(1.08)
            }

            100% {
                filter: brightness(1)
            }
        }

        @media (min-width:821px) and (max-width:1024px) {
            .medical-lantern-container {
                transform: scale(.85)
            }
        }

        @media (min-width:1025px) and (max-width:1280px) {
            .medical-lantern-container {
                transform: scale(.95)
            }
        }
    </style>

    <div class="tet_bottom"><img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgwruFlhClo3FUKNBQtDvqWYiDVOoi-IT7Jy4R11OU5HaOFR2N7CcX5sH4FWQI_GRoVrx4Hd5pVQREJ_QsAjvSA41v25TW0LEGW2jb8s3J2QwCrXp4qsMqdvxUZz9lglGyxL4YQxIbbf17zyqd99Rr28rDzx-foaXJRQ13kQUAblMtlt4U1rKMYbHkn5w/s16000/bottom-1.png" alt="Trang trí Tết phía dưới" /></div>

    <style type="text/css">
        .tet_bottom {
            position: fixed;
            bottom: 0;
            left: 80px;
            z-index: 99;
            width: 320px;
            pointer-events: none;
        }

        @media (max-width: 1331px) {

            .tet_left,
            .tet_right,
            .tet_bottom {
                display: none !important;
            }
        }
    </style>

</body>

</html>