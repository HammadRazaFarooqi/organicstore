
<?php
require_once './admin/config/database.php'; // This gives us the Database class

$database = new Database();
$pdo = $database->getConnection(); // Get the PDO connection

try {
  $catQuery = "SELECT c.id, c.name, c.description, c.status,
         (
             SELECT COUNT(*) 
             FROM products p 
             WHERE p.category_id = c.id AND p.status = 'active'
         ) AS product_count
  FROM categories c
  WHERE c.status = 'active'
  ORDER BY c.created_at DESC";

  $catStmt = $pdo->prepare($catQuery);
  $catStmt->execute();
  $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error fetching categories: " . $e->getMessage());
}

// Fetch all active products
$productsQuery = "
SELECT 
        p.id,
        p.name,
        p.description,
        p.price,
        p.image,
        p.status,
        c.name as category_name,
        c.id as category_id,
        d.name as discount_name,
        d.type as discount_type,
        d.value as discount_value,
        d.status as discount_status,
        d.start_date,
        d.end_date
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN discounts d ON p.discount_id = d.id
    WHERE p.status = 'active'
    ORDER BY p.name
";
$products = $pdo->query($productsQuery)->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <!-- For IE -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- For Resposive Device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=1"
    />
    <title>Organic Store | Responsive HTML5 Template</title>
    <!-- Favicon -->
    <link
      rel="apple-touch-icon"
      sizes="57x57"
      href="images/fav-icon/apple-icon-57x57.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="60x60"
      href="images/fav-icon/apple-icon-60x60.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="72x72"
      href="images/fav-icon/apple-icon-72x72.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="76x76"
      href="images/fav-icon/apple-icon-76x76.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="114x114"
      href="images/fav-icon/apple-icon-114x114.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="120x120"
      href="images/fav-icon/apple-icon-120x120.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="144x144"
      href="images/fav-icon/apple-icon-144x144.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="152x152"
      href="images/fav-icon/apple-icon-152x152.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="images/fav-icon/apple-icon-180x180.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="192x192"
      href="images/fav-icon/android-icon-192x192.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="images/fav-icon/favicon-32x32.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="96x96"
      href="images/fav-icon/favicon-96x96.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="images/fav-icon/favicon-16x16.png"
    />

    <!-- Custom Css -->
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/responsive.css" />
    <link rel="stylesheet" type="text/css" href="css/generic.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />


    <!-- Fixing Internet Explorer ______________________________________-->

    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <script src="vendor/html5shiv.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="main_page">
      <!-- Header *******************************  -->
     

      <!-- Menu ******************************* -->
      <div class="theme_menu color1_bg" style="position: fixed; top: 0; width: 100%; z-index: 9999;">
        <div class="container">
          <nav class="menuzord pull-left" id="main_menu">
            <ul class="menuzord-menu">
            <li>
                <a href="index.php">
                  <img src="images/logo/logo2.png" alt="Logo" class="img-fluid" style="width:50px; height:50px; margin-top:10px; margin-bottom:-15px" />
                </a>
              </li>
              <li ><a href="index.php" >Home</a></li>
              <li><a href="about-us.html">About us</a></li>
              <li class="current_page">
                <a href="#">store</a>
                <ul class="dropdown">
                  <li><a href="shop.php">Groceries Items</a></li>
                  <li><a href="shop.php">Halal Meat</a></li>
                </ul>
              </li>
              <li><a href="contact.html">Contact us</a></li>
            </ul>
            <!-- End of .menuzord-menu -->
          </nav>
          <!-- End of #main_menu -->

          <!-- ******* Cart And Search Option ******** -->
          <div class="nav_side_content pull-right">
  <ul class="icon_header">
    <li class="border_round tran3s">
      <a href="#"><i class="fab fa-facebook-f"></i></a>
    </li>
    <li class="border_round tran3s">
      <a href="#"><i class="fab fa-instagram"></i></a>
    </li>
    <li class="border_round tran3s">
      <a href="#"><i class="fab fa-tiktok"></i></a>
    </li>
  </ul>
</div>


          <!-- End of .nav_side_content -->
        </div>
        <!-- End of .container -->
      </div>
      <!-- End of .theme_menu -->

      <section
        class="breadcrumb-area"
        style="background-image: url(images/background/2.jpg)"
      >
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <div class="breadcrumbs text-center">
                <h1>our store</h1>
                <h4>Welcome to certified online organic products suppliersr</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="breadcrumb-bottom-area">
          <div class="container">
            <div class="row">
              <div class="col-lg-8 col-md-5 col-sm-5">
                <ul>
                  <li><a href="#">Home</a></li>
                  <li>
                    <a href=""><i class="fa fa-angle-right"></i></a>
                  </li>
                  <li><a href="#">Gallery</a></li>
                  <li>
                    <a href=""><i class="fa fa-angle-right"></i></a>
                  </li>
                  <li>our store</li>
                </ul>
              </div>
              <div class="col-lg-4 col-md-7 col-sm-7">
                <p>We provide <span>100% organic</span> products</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Shop Page Content************************ -->
      <div class="shop_page featured-product">
        <div class="container">
          <div class="row">
            <div class="col-lg-9 col-md-8 col-sm-12 col-sx-12">
              <div class="row">
                

              <?php foreach ($products as $product): ?>
  <div class="col-md-4 col-sm-6 col-xs-12 default-item" style="display: inline-block">
  <div class="product-card inner-box">
    <div class="single-item center">

      <figure class="image-box">
        <img class="product-image" 
             src="admin/assets/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>">
        <div class="product-model hot"><?php echo ucfirst($product['category_name']); ?></div>
      </figure>

      <div class="content">
        <h3><a href="#"><?php echo htmlspecialchars($product['name']); ?></a></h3>
        <div class="rating">
          <span class="fa fa-star"></span><span class="fa fa-star"></span>
          <span class="fa fa-star"></span><span class="fa fa-star"></span>
          <span class="fa fa-star"></span>
        </div>

        <div class="price">
          <?php if (!empty($product['discount_name']) && 
                    $product['discount_status'] == 'active' &&
                    strtotime($product['start_date']) <= time() && 
                    strtotime($product['end_date']) >= time()): ?>
            <?php
              $original = (float)$product['price'];
              $discountAmount = ($product['discount_type'] === 'percentage') 
                  ? $original * ($product['discount_value'] / 100)
                  : $product['discount_value'];
              $discounted = max(0, $original - $discountAmount);
            ?>
            <small style="text-decoration: line-through; color: #999; padding-right: 1rem">
              $<?php echo number_format($original, 2); ?>
            </small>
            <span style="color: #e60000; font-weight: bold;">
              $<?php echo number_format($discounted, 2); ?>
            </span>
            <div class="discount-badge" style="color: green; font-size: 12px;">
              <?php echo ($product['discount_type'] === 'percentage') 
                  ? number_format($product['discount_value']) . '% OFF' 
                  : 'Save $' . number_format($product['discount_value'], 2); ?>
            </div>
          <?php else: ?>
            <span style="font-weight: bold;">$<?php echo number_format($product['price'], 2); ?></span>
          <?php endif; ?>
</div>
        </div>
        <div class="overlay-box">
            <div class="inner">
              <div class="top-content">
                <ul>
                  <li class="tultip-op">
                    <span class="tultip"><i class="fa fa-sort-desc"></i>VIEW DETAILS</span>
                    <!-- <a href="/organicstore/shop-single.php"></a> -->
                    <a href="/shop-single.php?id=<?php echo $product['id']; ?>">
                    <span class="icon-icon-32846"></span>
                    </a>

                  </li>
                </ul>
              </div>
              <div class="bottom-content">
                <h4><a href="">Description:</a></h4>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

              </div>
            </div>

            <!-- _______________________ SIDEBAR ____________________ -->
            <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 sidebar_styleTwo">
              <div class="wrapper">
                <div class="sidebar_search">
                  <form action="#">
                    <input type="text" />
                    <button class="tran3s color1_bg">
                      <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                  </form>
                </div>
                <!-- End of .sidebar_styleOne -->

                <div class="sidebar_categories">
                  <div class="theme_inner_title">
                    <h4>Categories</h4>
                  </div>
                  <ul>
  <?php foreach ($categories as $cat): ?>
    <li>
      <a href="?category=<?php echo urlencode($cat['id']); ?>" class="tran3s">
        <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['product_count']; ?>)
      </a>
    </li>
  <?php endforeach; ?>
</ul>

                </div>
                <!-- End of .sidebar_categories -->

                <!-- <div class="price_filter wow fadeInUp">
                  <div class="theme_inner_title">
                    <h4>Filter By Price</h4>
                  </div>
                  <div class="single-sidebar price-ranger">
                    <div id="slider-range"></div>
                    <div class="ranger-min-max-block">
                      <input type="submit" value="Filter" />
                      <span>Price:</span>
                      <input type="text" readonly class="min" />
                      <span>-</span>
                      <input type="text" readonly class="max" />
                    </div>
                  </div> -->
                  <!-- /price-ranger -->
                <!-- </div> -->
                <!-- /price_filter -->

                <!-- <div class="best_sellers clear_fix wow fadeInUp">
                  <div class="theme_inner_title">
                    <h4>popular products</h4>
                  </div>

                  <div class="best_selling_item clear_fix border">
                    <div class="img_holder float_left">
                      <img src="images/shop/9.png" alt="image" />
                    </div>

                    <div class="text float_left">
                      <a href="shop-single.php"><h6>Turmeric Powder</h6></a>
                      <ul>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                      </ul>
                      <span>$ 15.00</span>
                    </div>
                  </div> -->
                  <!-- End of .best_selling_item -->

                  <!-- <div class="best_selling_item clear_fix border">
                    <div class="img_holder float_left">
                      <img src="images/shop/10.png" alt="image" />
                    </div> -->
                    <!-- End of .img_holder -->

                    <!-- <div class="text float_left">
                      <a href="shop-single.php"><h6>Pure Jeans Coffee</h6></a>
                      <ul>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                      </ul>
                      <span>$ 34.00</span>
                    </div> -->
                    <!-- End of .text -->
                  <!-- </div> -->
                  <!-- End of .best_selling_item -->

                  <!-- <div class="best_selling_item clear_fix">
                    <div class="img_holder float_left">
                      <img src="images/shop/11.png" alt="image" />
                    </div> -->
                    <!-- End of .img_holder -->

                    <!-- <div class="text float_left">
                      <a href="shop-single.php"><h6>Columbia Chocolate</h6></a>
                      <ul>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        <li><i class="fa fa-star" aria-hidden="true"></i></li>
                      </ul>
                      <span>$ 34.99</span>
                    </div> -->
                    <!-- End of .text -->
                  <!-- </div> -->
                  <!-- End of .best_selling_item -->
                <!-- </div> -->
                <!-- End of .best_sellers -->

                <!-- <div class="sidebar_tags wow fadeInUp">
                  <div class="theme_inner_title">
                    <h4>product Tags</h4>
                  </div>

                  <ul>
                    <li><a href="#" class="tran3s">fruits</a></li>
                    <li><a href="#" class="tran3s">Cosmetics</a></li>
                    <li><a href="#" class="tran3s">Farmers</a></li>
                    <li><a href="#" class="tran3s">Healthy</a></li>
                    <li><a href="#" class="tran3s">Catering</a></li>
                    <li><a href="#" class="tran3s">Chemical</a></li>
                    <li><a href="#" class="tran3s">Post Format</a></li>
                    <li><a href="#" class="tran3s">Industry</a></li>
                    <li><a href="#" class="tran3s">Research</a></li>
                  </ul>
                </div> -->
                <!-- End of .sidebar_tags -->
              </div>
              <!-- End of .wrapper -->
            </div>
            <!-- End of .sidebar_styleTwo -->
          </div>
          <!-- End of .row -->
        </div>
        <!-- End of .container -->
      </div>
      <!-- End of .shop_page -->

      <!-- Footer************************* -->
      <footer>
      <div class="main_footer py-5 bg-light">
        <div class="container">
      <div class="row">

        <!-- Footer Logo & Description -->
        <div class="col-lg-3 col-md-6 mb-4 footer_logo">
        <h3 style="color: white;" class="h4 fw-bold">PUNJAB GROCERS & <br> HALAL MEAT</h3>
        <p class="small text-muted">
            Denouncing pleasures and praising pain was born and I will give you a complete account of the system.
          </p>
          <a href="#" class="small text-decoration-none">Developed by Tech Joint Solution</a>
        </div>

        <!-- Categories -->
        <div class="col-lg-3 col-md-6 mb-4 footer_subscribe">
          <h5 class="fw-semibold">Categories</h5>
          <ul class="list-unstyled small">
            <li><a href="#" class="text-muted"><i class="fa fa-angle-right me-2"></i>Groceries Items</a></li>
            <li><a href="#" class="text-muted"><i class="fa fa-angle-right me-2"></i>Halal Meat</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="col-lg-3 col-md-6 mb-4 footer_contact">
          <h5 class="fw-semibold">Get In Touch</h5>
          <ul class="list-unstyled small">
            <li><a href="mailto:punjabgrocerhalalmeat@gmail.com" class="text-muted"><i class="fa fa-envelope" style="margin-right:10px;"></i>punjabgrocerhalalmeat@gmail.com</a></li>
            <li><a href="tel:+19054517666" class="text-muted"><i class="fa fa-phone me-2"style="margin-right:10px;"></i>(905) 451‑7666</a></li>
            <li><a href="#" class="text-muted"><i class="fa fa-home me-2"style="margin-right:10px;"></i>5 Montpelier St, Unit 106, 107, Brampton, ON L6Y 6H4</a></li>
          </ul>
        </div>

        <!-- Business Hours -->
        <div class="col-lg-3 col-md-6 mb-4 footer_hours">
          <h5 class="fw-semibold">Business Hours</h5>
          <p class="small text-muted mb-1">Monday - Friday: 09:00 AM to 07:00 PM</p>
          <p class="small text-muted mb-1">Saturday: 10:00 AM to 05:00 PM</p>
          <p class="small text-muted">Sunday: <span class="text-danger fw-bold">Closed</span></p>
        </div>

      </div>
    </div>
  </div>

  <!-- Bottom Footer -->
  <div class="bottom_footer text-center py-3 bg-dark text-white">
    <p class="mb-0 small">© 2025 Punjab Grocers. All rights reserved.</p>
  </div>
</footer>
      <!-- Scroll Top Button -->
      <button class="scroll-top tran3s color2_bg">
        <span class="fa fa-angle-up"></span>
      </button>
      <!-- pre loader  -->
      <div id="loader-wrapper">
        <div id="loader"></div>
      </div>

      <!-- Js File_________________________________ -->

      <!-- j Query -->
      <script type="text/javascript" src="js/jquery-2.1.4.js"></script>
      <!-- Bootstrap JS -->
      <script type="text/javascript" src="js/bootstrap.min.js"></script>

      <!-- Vendor js _________ -->
      <!-- Google map js -->
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRvBPo3-t31YFk588DpMYS6EqKf-oGBSI"></script>
      <!-- Gmap Helper -->
      <script src="js/gmap.js"></script>
      <!-- owl.carousel -->
      <script type="text/javascript" src="js/owl.carousel.min.js"></script>
      <!-- ui js -->
      <script type="text/javascript" src="js/jquery-ui.min.js"></script>
      <!-- Responsive menu-->
      <script type="text/javascript" src="js/menuzord.js"></script>
      <!-- revolution -->
      <script src="vendor/revolution/jquery.themepunch.tools.min.js"></script>
      <script src="vendor/revolution/jquery.themepunch.revolution.min.js"></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.slideanims.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.layeranimation.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.navigation.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.kenburn.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.actions.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.parallax.min.js"
      ></script>
      <script
        type="text/javascript"
        src="vendor/revolution/revolution.extension.migration.min.js"
      ></script>

      <!-- landguage switcher js -->
      <script
        type="text/javascript"
        src="js/jquery.polyglot.language.switcher.js"
      ></script>
      <!-- Fancybox js -->
      <script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
      <!-- js count to -->
      <script type="text/javascript" src="js/jquery.appear.js"></script>
      <script type="text/javascript" src="js/jquery.countTo.js"></script>
      <!-- WOW js -->
      <script type="text/javascript" src="js/wow.min.js"></script>

      <script type="text/javascript" src="js/SmoothScroll.js"></script>

      <script src="js/bootstrap-select.min.js"></script>
      <script src="js/jquery.mixitup.min.js"></script>
      <!-- Theme js -->
      <script type="text/javascript" src="js/theme.js"></script>
      <script type="text/javascript" src="js/google-map.js"></script>
    </div>
    <!-- End of .main_page -->
  </body>
</html>
