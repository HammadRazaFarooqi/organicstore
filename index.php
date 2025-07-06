
<?php
require_once './admin/config/database.php'; // This gives us the Database class

$database = new Database();
$pdo = $database->getConnection(); // Get the PDO connection

// Fetch all active categories
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = $pdo->query($categoriesQuery)->fetchAll(PDO::FETCH_ASSOC);

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
order by name limit 6"; // Limit to 6 products for the homepage
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
    <title>Punjab Grocers & Halal Meat </title>
    <!-- Favicon -->
    <link
      rel="apple-touch-icon"
      sizes="57x57"
      href="images/fav-icon/apple-icon-57x57.png"
    />

    <!-- Custom Css -->
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/generic.css" />
    <link rel="stylesheet" type="text/css" href="css/responsive.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />


    <!-- Fixing Internet Explorer ______________________________________-->

    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <script src="vendor/html5shiv.js"></script>
    <![endif]-->


    
  </head>

  <body>
    <div class="main_page">

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
              <li class="current_page"><a href="index.php" >Home</a></li>
              <li><a href="about-us.html">About us</a></li>
              <li>
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
      <a href="https://www.facebook.com/Punjabgrocercanada"><i class="fab fa-facebook-f"></i></a>
    </li>
    <li class="border_round tran3s">
      <a href="https://www.instagram.com/punjabgrocershalalmeat/"><i class="fab fa-instagram"></i></a>
    </li>
    <li class="border_round tran3s">
      <a href="https://www.tiktok.com/@punjabgrocershalalmeat"><i class="fab fa-tiktok"></i></a>
    </li>
  </ul>
</div>


          <!-- End of .nav_side_content -->
        </div>
        <!-- End of .container -->
      </div>
      <!-- End of .theme_menu -->

      <!-- Banner ____________________________________ -->
      <div id="banner">
  <div class="swiper mySwiper">
    <div class="swiper-wrapper">
      <!-- Slide 1 -->
      <div class="swiper-slide">
        <img src="images/home/slide-1.jpg" alt="Slide 1" style="margin: 72px 0;" />
      </div>

      <!-- Slide 2 -->
      <div class="swiper-slide">
        <img src="images/home/slide-2.jpg" alt="Slide 2" style="margin: 72px 0;"  />
      </div>

      <!-- Slide 3 -->
      <div class="swiper-slide">
        <img src="images/home/slide-3.jpg" alt="Slide 3" style="margin: 72px 0;"  />
    </div>

  </div>
</div>

      <!--feature Section-->
      <section class="featured-product">
        <div class="container">
          <div class="theme_title center">
            <h3>Groceries Items</h3>
          </div>

          <!--Filter-->
          <div class="filters text-center">
  <ul class="filter-tabs filter-btns clearfix">
  <li class="filter active" data-role="button" data-filter="all">
  <span class="txt">All Products</span>
</li>
    <?php foreach ($categories as $category): ?>
      <?php $catClass = strtolower(preg_replace('/\s+/', '', $category['name'])); ?>
      <li class="filter" data-role="button" data-filter=".<?php echo $catClass; ?>">
        <span class="txt"><?php echo htmlspecialchars($category['name']); ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

          <!--Products-->
          <div class="clearfix"></div>




<div class="row filter-list clearfix" id="MixItUpContainer">
  <?php foreach ($products as $product): ?>
    <?php $categoryClass = strtolower(preg_replace('/\s+/', '', $product['category_name'])); ?>
    <div class="col-md-4 col-sm-6 col-xs-12 default-item mix <?php echo $categoryClass; ?>" style="display: inline-block;">
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
                    <a href="/shop-single.php?id=<?php echo $product['id']; ?>">
                    <span class="icon-icon-32846"></span>                  
                  </li>
                </ul>
              </div>
              <div class="bottom-content">
                <h4><a href="#">Description:</a></h4>
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
        <span class="all_products">
          <h5><a href="./shop.php" class="view_all_products">View All</a></h5>
        </span>
      </section>
      <!-- End of section -->

      <!-- Request Quote -->
      <section class="why_choose_us">
        <div
          class="theme_title_bg"
          style="background-image: url(images/background/)"
        >
          <div class="theme_title center">
            <div class="container">
              <h2>Why to Choose Us</h2>
              <p>
                There are many variations of passages of Lorem Ipsum available,
                but the majority have suffered <br />alteration in some form, by
                injected humour.
              </p>
            </div>
          </div>
        </div>

        <div class="container">
          <!-- End of .theme_title_center -->

          <div class="row">
            <!-- ______________ Item _____________ -->
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="choose_us_item tran3s">
                <div class="icon p_color_bg border_round float_left">
                  <span class="ficon icon-fruit-1"></span>
                </div>
                <!-- End of .icon -->
                <div class="text float_left">
                  <h5 class="tran3s"> Wide Selection of Quality Products</h5>
                  <p class="tran3s">
                  
Fresh produce, pantry staples, dairy, snacks & beverages — all under one roof.
                  </p>
                </div>
                <!-- End of .text -->
                <div class="clear_fix"></div>
              </div>
              <!-- End of .choose_us_item -->
            </div>
            <!-- End of .col -->

            <!-- ______________ Item _____________ -->
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="choose_us_item tran3s">
                <div class="icon p_color_bg border_round float_left">
                  <span class="ficon icon-wheat"></span>
                </div>
                <!-- End of .icon -->
                <div class="text float_left">
                  <h5 class="tran3s">Fresh Halal Meat Daily</h5>
                  <p class="tran3s">
                   From beef, lamb, and chicken to goat and specialty cuts — prepared fresh every day, just the way you like.
                  </p>
                </div>
                <!-- End of .text -->
                <div class="clear_fix"></div>
              </div>
              <!-- End of .choose_us_item -->
            </div>
            <!-- End of .col -->

            <!-- ______________ Item _____________ -->
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="choose_us_item tran3s">
                <div class="icon p_color_bg border_round float_left">
                  <span class="ficon icon-food-2"></span>
                </div>
                <!-- End of .icon -->
                <div class="text float_left">
                  <h5 class="tran3s">Affordable & Accessible</h5>
                  <p class="tran3s">
                   Enjoy competitive prices without compromising on quality — right in your neighborhood.
                  </p>
                </div>
                <!-- End of .text -->
                <div class="clear_fix"></div>
              </div>
              <!-- End of .choose_us_item -->
            </div>
            <!-- End of .col -->

            <!-- ______________ Item _____________ -->
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="choose_us_item tran3s">
                <div class="icon p_color_bg border_round float_left">
                  <span class="ficon icon-fruit"></span>
                </div>
                <!-- End of .icon -->
                <div class="text float_left">
                  <h5 class="tran3s">Clean, Friendly & Convenient</h5>
                  <p class="tran3s">
                   Shop in a clean, organized store with helpful staff ready to make your visit easy.
                  </p>
                </div>
                <!-- End of .text -->
                <div class="clear_fix"></div>
              </div>
              <!-- End of .choose_us_item -->
            </div>
            <!-- End of .col -->
          </div>
        </div>
        <!-- End of .container -->
      </section>
      <!-- End of why chooreus -->

      <!--Testimonials Section-->
      <section
        class="testimonials-section"
        style="background-image: url((images/background/Testimonial.jpg)"
      >Testimonial.jpg
        <div class="container">
          <div class="theme_title">
            <h2>testimonials</h2>
          </div>
          <div class="testimonials-carousel">
            <!--Slide Item-->
            <div class="slide-item">
              <div class="inner-box">
                <div class="content">
                  <div class="text-bg">
                    <div class="quote-icon">
                      <span class="fa fa-quote-left"></span>
                    </div>
                    <div class="text">
                      Who do not know how to pursue an sed pleasure rationally
                      encounter that are extremely win painful nor again is
                      there anyone who loves or pursues or desires obtain pain
                      itself circumstances.
                    </div>
                  </div>
                  <div class="info clearfix">
                  <div class="author-thumb avatar">
                    <span>W</span>
                  </div>
                    <div class="author">William Border</div>
                    <div class="author-title">Designer</div>
                  </div>
                </div>
              </div>
            </div>

            <!--Slide Item-->
            <div class="slide-item">
              <div class="inner-box">
                <div class="content">
                  <div class="text-bg">
                    <div class="quote-icon">
                      <span class="fa fa-quote-left"></span>
                    </div>
                    <div class="text">
                      Who do not know how to pursue an sed pleasure rationally
                      encounter that are extremely win painful nor again is
                      there anyone who loves or pursues or desires obtain pain
                      itself circumstances.
                    </div>
                  </div>
                  <div class="info clearfix">
                  <div class="author-thumb avatar">
  <span>J</span>
</div>
                    <div class="author">Jessy Federar</div>
                    <div class="author-title">Cor.Manager</div>
                  </div>
                </div>
              </div>
            </div>

            <!--Slide Item-->
            <div class="slide-item">
              <div class="inner-box">
                <div class="content">
                  <div class="text-bg">
                    <div class="quote-icon">
                      <span class="fa fa-quote-left"></span>
                    </div>
                    <div class="text">
                      Who do not know how to pursue an sed pleasure rationally
                      encounter that are extremely win painful nor again is
                      there anyone who loves or pursues or desires obtain pain
                      itself circumstances.
                    </div>
                  </div>
                  <div class="info clearfix">
                  <div class="author-thumb avatar">
  <span>M</span>
</div>
                    <div class="author">Mark Antony</div>
                    <div class="author-title">Designer</div>
                  </div>
                </div>
              </div>
            </div>
           
          </div>
        </div>
      </section>

      <!-- Partner Logo********************** -->

      <div class="partners wow fadeInUp">
        <div class="container">
          <div id="partner_logo" class="owl-carousel owl-theme">
            <div class="item">
              <img src="images/partner-logo/1.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/2.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/2.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/4.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/1.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/2.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/3.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/4.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/1.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/2.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/3.png" alt="logo" />
            </div>
            <div class="item">
              <img src="images/partner-logo/4.png" alt="logo" />
            </div>
          </div>
          <!-- End .partner_logo -->
        </div>
      </div>

      <section class="call-out">
        <div class="container">
          <div class="float_left">
            <h2>Subscribe For Newsletter</h2>
            <p>We send you latest news couple a month ( No Spam).</p>
          </div>
          <div class="float_right">
            <div class="contact-box">
              <form id="myForm">
                <input
                  type="hidden"
                  name="access_key"
                  value="547eff1c-5d39-4d72-b874-07f38a8a2ccd"
                />

                <div class="form-group">
                  <input
                    type="text"
                    name="username"
                    placeholder="Your Name*"
                    required
                  />
                </div>

                <div class="form-group">
                  <input
                    type="email"
                    name="email"
                    placeholder="Email Address*"
                    required
                  />
                </div>

                <div class="form-group">
                  <button type="submit">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>

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
      <!--Popup js-->
      <script src="js/popup.js"></script>
      <!--Newsletter.js-->
      <script src="js/newsletter.js"></script>
      <!-- Swiper CSS -->
      <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
      />

      
   <!-- MixItUp JS -->
   <script>
$(document).ready(function() {
    // Initialize MixItUp for filtering
    $('#MixItUpContainer').mixItUp({
        selectors: {
            target: '.mix',
            filter: '.filter'
        },
        animation: {
            duration: 300
        }
    });
  
  // Handle filter button clicks
    $('.filter').on('click', function() {
        // Remove active class from all filters
        $('.filter').removeClass('active');
        // Add active class to clicked filter
        $(this).addClass('active');
        
        // Get filter value
        var filterValue = $(this).data('filter');
        
        // Apply filter
        if (filterValue === 'all') {
            $('#MixItUpContainer').mixItUp('filter', 'all');
        } else {
            $('#MixItUpContainer').mixItUp('filter', filterValue);
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const swiper = new Swiper(".mySwiper", {
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });
</script>




    <!-- End of .main_page -->
  </body>
</html>
