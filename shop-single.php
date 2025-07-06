<?php
require_once './admin/config/database.php'; // Include DB connection

// Initialize $pdo from Database class
$database = new Database();
$pdo = $database->getConnection();
// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product not found.");
}

$product_id = intval($_GET['id']); // Sanitize input

// Fetch product details
try {
    $query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.id = :id AND p.status = 'active'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found or inactive.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
// Fetch all active categories
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
?>


    
    
    
    
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8" />
        <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
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
      <div class="theme_menu color1_bg" style="position: fixed; top: 0; width: 100%; z-index: 9999;">
        <div class="container">
          <nav class="menuzord pull-left" id="main_menu">
            <ul class="menuzord-menu">
              <li>
                <a href="index.php">
                  <img src="images/logo/logo2.png" alt="Logo" class="img-fluid" style="width:80px; height:80px; margin-top:-10px; margin-bottom:-15px" />
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
                <h1>single product</h1>
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
                  <li>single product</li>
                </ul>
              </div>
              <div class="col-lg-4 col-md-7 col-sm-7">
                <p>We provide <span>100% organic</span> products</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Single Shop page content ________________ -->

      <div class="shop_single_page">
        <div class="container">
          <div class="row">
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
											<img src="images/shop/9.png" alt="image">
										</div> End of .img_holder -->

                <!-- <div class="text float_left">
											<a href="shop-single.html"><h6>Turmeric Powder</h6></a>
											<ul>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
											</ul>
											<span>$ 15.00</span> -->
                <!-- </div> End of .text -->
                <!-- </div> End of .best_selling_item -->

                <!-- <div class="best_selling_item clear_fix border">
										<div class="img_holder float_left">
											<img src="images/shop/10.png" alt="image">
										</div> End of .img_holder -->

                <!-- <div class="text float_left">
											<a href="shop-single.html"><h6>Pure Jeans Coffee</h6></a>
											<ul>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
											</ul>
											<span>$ 34.00</span>
										</div> End of .text -->
                <!-- </div> End of .best_selling_item -->

                <!-- <div class="best_selling_item clear_fix">
										<div class="img_holder float_left">
											<img src="images/shop/11.png" alt="image">
										</div> End of .img_holder -->

                <!-- <div class="text float_left">
											<a href="shop-single.html"><h6>Columbia Chocolate</h6></a>
											<ul>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
												<li><i class="fa fa-star" aria-hidden="true"></i></li>
											</ul>
											<span>$ 34.99</span>
										</div> End of .text -->
                <!-- </div> End of .best_selling_item -->
                <!-- </div> End of .best_sellers -->

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
            <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 product_details">
              <div class="wrapper">
               <!-- Product Detail Section -->
<div class="product_top_section clear_fix">
    <div class="img_holder float_left">
        <img src="admin/assets/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             class="img-responsive" />
    </div>

    <div class="item_description float_left">
        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
        <br>
        <!-- <ul>
            <li><i class="fa fa-star" aria-hidden="true"></i></li>
            <li><i class="fa fa-star" aria-hidden="true"></i></li>
            <li><i class="fa fa-star" aria-hidden="true"></i></li>
            <li><i class="fa fa-star" aria-hidden="true"></i></li>
            <li><i class="fa fa-star" aria-hidden="true"></i></li>
            <li>(2 Customer Reviews)</li>
        </ul> -->
        <span class="item_price">$<?php echo number_format($product['price'], 2); ?></span>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <span class="check_location">Check Delivery Option at Your Location:</span>

    </div>
</div>
                <!-- End of .product_top_section -->

                <!-- __________________ Product review ___________________ -->
                <div class="product-review-tab">
                  <ul class="nav nav-pills">
                    <li><a data-toggle="pill" href="#tab1">Description</a></li>
                    <li class="active">
                      <a data-toggle="pill" href="#tab2">Reviews(2)</a>
                    </li>
                  </ul>

                  <div class="tab-content">
                    <div id="tab1" class="tab-pane fade">
                      <p>
                        It is a long established fact that a reader will be
                        distracted by the readable content of a page when
                        looking at its layout The point of using Lorem Ipsum is
                        that it has a more-or-less normal distribution of
                        letters, as opposed to using Content here, content
                        here', making it look like readable English. Many
                        desktop publishing packages and web page editors now use
                        Lorem Ipsum as their default model text, and a search
                        for 'lorem ipsum' will uncover many web sites still in
                        their infancy. Various versions have evolved over the
                        years on purpose.
                      </p>
                      <p style="margin-top: 10px">
                        Distracted by the readable content of a page when
                        looking at its layout. The point of using Lorem Ipsum is
                        that it has a more-or-less normal distribution of
                        letters, as opposed to using 'Content here, content
                        here', making it look like readable English. Many
                        desktop publishing packages and web page editors when
                        looking.
                      </p>
                    </div>
                    <!-- End of #tab1 -->

                    <div id="tab2" class="tab-pane fade in active">
                      <!-- Single Review -->
                      <div class="item_review_content clear_fix">
                        <div class="img_holder float_left">
                          <img src="images/gallery/a1.jpg" alt="img" />
                        </div>
                        <!-- End of .img_holder -->

                        <div class="text float_left">
                          <div class="sec_up clear_fix">
                            <h6 class="float_left">Michel Kong</h6>
                            <div class="float_right">
                              <span class="p_color"
                                >21/08/2015 &nbsp;at &nbsp;09.45</span
                              >
                              <ul>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                              </ul>
                            </div>
                          </div>
                          <!-- End of .sec_up -->
                          <p>
                            Many web sites still sed in their infancy Various
                            versions have sed evolveed over the years, sometimes
                            by there accident, sometimes all times purpose
                            rationally sed encounter se consequencess ut that
                            are at sed extremely well painful or again is there
                            anyone who loves or seds of pursues.
                          </p>

                          <div class="up_down_nav">
                            <a href="#"
                              ><i class="fa fa-angle-up" aria-hidden="true"></i
                            ></a>
                            <a href="#"
                              ><i
                                class="fa fa-angle-down"
                                aria-hidden="true"
                              ></i
                            ></a>
                          </div>
                          <!-- End of .up_down_nav -->

                          <div class="reply_share_area">
                            <a href="#" class="tran3s">Reply</a>
                            <a href="#" class="tran3s">Share</a>
                          </div>
                          <!-- End of .reply_share_area -->
                        </div>
                        <!-- End of .text -->
                      </div>
                      <!-- End of .item_review_content -->

                      <!-- Single Review -->
                      <div class="item_review_content clear_fix">
                        <div class="img_holder float_left">
                          <img src="images/gallery/a2.jpg" alt="img" />
                        </div>
                        <!-- End of .img_holder -->

                        <div class="text float_left">
                          <div class="sec_up clear_fix">
                            <h6 class="float_left">Jeorge Meckey</h6>
                            <div class="float_right">
                              <span class="p_color"
                                >26/08/2015 &nbsp;at &nbsp;05.30</span
                              >
                              <ul>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i class="fa fa-star" aria-hidden="true"></i>
                                </li>
                                <li>
                                  <i
                                    class="fa fa-star-half-o"
                                    aria-hidden="true"
                                  ></i>
                                </li>
                              </ul>
                            </div>
                          </div>
                          <!-- End of .sec_up -->
                          <p>
                            Know how to pursue pleasure rationally encounter
                            consequences that are extremely painful nor again is
                            there anyone who loves or pursues or desires to
                            obtain pain seds of itself, because it is pain,
                            under because occasionally circumstances occur in
                            which toil great pleasure.
                          </p>

                          <div class="up_down_nav">
                            <a href="#"
                              ><i class="fa fa-angle-up" aria-hidden="true"></i
                            ></a>
                            <a href="#"
                              ><i
                                class="fa fa-angle-down"
                                aria-hidden="true"
                              ></i
                            ></a>
                          </div>
                          <!-- End of .up_down_nav -->

                          <div class="reply_share_area">
                            <a href="#" class="tran3s">Reply</a>
                            <a href="#" class="tran3s">Share</a>
                          </div>
                          <!-- End of .reply_share_area -->
                        </div>
                        <!-- End of .text -->
                      </div>
                      <!-- End of .item_review_content -->

                      <!-- <div class="add_your_review">
                        <div class="theme_inner_title">
                          <h4>Add Your Review</h4>
                        </div>

                        <span>Your Rating</span>
                        <ul>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        </ul>
                        <ul>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        </ul>
                        <ul>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        </ul>
                        <ul>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        </ul>
                        <ul class="fix_border">
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                          <li><i class="fa fa-star" aria-hidden="true"></i></li>
                        </ul>

                        <form action="#">
                          <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                              <input type="text" placeholder="Name*" />
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                              <input type="email" placeholder="Email*" />
                            </div>
                            <div
                              class="col-lg-12 col-md-12 col-sm-12 col-xs-12"
                            >
                              <textarea placeholder="Your Review..."></textarea>
                            </div>
                          </div>
                          <button class="color1_bg tran3s">Add A Review</button>
                        </form>
                      </div> -->
                      <!-- End of .add_your_review -->
                    </div>
                    <!-- End of #tab2 -->
                  </div>
                  <!-- End of .tab-content -->
                </div>
                <!-- End of .product-review-tab -->

                <!-- <div class="related_product">
                  <div class="theme_title">
                    <h3>Related Products</h3>
                  </div> -->

                  <!-- Shop Page Content************************ -->
                  <!-- <div class="shop_page featured-product">
                    <div class="row"> -->
                      <!--Default Item-->
                      <!-- <div
                        class="col-md-4 col-sm-6 col-xs-12 default-item"
                        style="display: inline-block"
                      >
                        <div class="inner-box">
                          <div class="single-item center">
                            <figure class="image-box">
                              <img src="images/shop/1.png" alt="" />
                              <div class="product-model new">New</div>
                            </figure>
                            <div class="content">
                              <h3>
                                <a href="shop-single.html">Turmeric Powder</a>
                              </h3>
                              <div class="rating">
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                              </div>
                              <div class="price">
                                $12.99 <span class="prev-rate">$14.99</span>
                              </div>
                            </div>
                            <div class="overlay-box">
                              <div class="inner">
                                <div class="top-content">
                                  <ul>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-eye"></span
                                      ></a>
                                    </li>
                                    <li class="tultip-op">
                                      <span class="tultip"
                                        ><i class="fa fa-sort-desc"></i>ADD TO
                                        CART</span
                                      ><a href="#"
                                        ><span class="icon-icon-32846"></span
                                      ></a>
                                    </li>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-heart-o"></span
                                      ></a>
                                    </li>
                                  </ul>
                                </div>
                                <div class="bottom-content">
                                  <h4><a href="#">It Contains:</a></h4>
                                  <p>
                                    35% of organic raisins 55% of oats and 10%
                                    of butter.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div> -->

                      <!--Default Item-->
                      <!-- <div
                        class="col-md-4 col-sm-6 col-xs-12 default-item"
                        style="display: inline-block"
                      >
                        <div class="inner-box">
                          <div class="single-item center">
                            <figure class="image-box">
                              <img src="images/shop/2.png" alt="" />
                            </figure>
                            <div class="content">
                              <h3>
                                <a href="shop-single.html">Turmeric Powder</a>
                              </h3>
                              <div class="rating">
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                              </div>
                              <div class="price">
                                $12.99 <span class="prev-rate">$14.99</span>
                              </div>
                            </div>
                            <div class="overlay-box">
                              <div class="inner">
                                <div class="top-content">
                                  <ul>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-eye"></span
                                      ></a>
                                    </li>
                                    <li class="tultip-op">
                                      <span class="tultip"
                                        ><i class="fa fa-sort-desc"></i>ADD TO
                                        CART</span
                                      ><a href="#"
                                        ><span class="icon-icon-32846"></span
                                      ></a>
                                    </li>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-heart-o"></span
                                      ></a>
                                    </li>
                                  </ul>
                                </div>
                                <div class="bottom-content">
                                  <h4><a href="#">It Contains:</a></h4>
                                  <p>
                                    35% of organic raisins 55% of oats and 10%
                                    of butter.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div> -->

                      <!--Default Item-->
                      <!-- <div
                        class="col-md-4 col-sm-6 col-xs-12 default-item"
                        style="display: inline-block"
                      >
                        <div class="inner-box">
                          <div class="single-item center">
                            <figure class="image-box">
                              <img src="images/shop/3.png" alt="" />
                              <div class="product-model hot">Hot</div>
                            </figure>
                            <div class="content">
                              <h3>
                                <a href="shop-single.html">Turmeric Powder</a>
                              </h3>
                              <div class="rating">
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                              </div>
                              <div class="price">
                                $12.99 <span class="prev-rate">$14.99</span>
                              </div>
                            </div>
                            <div class="overlay-box">
                              <div class="inner">
                                <div class="top-content">
                                  <ul>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-eye"></span
                                      ></a>
                                    </li>
                                    <li class="tultip-op">
                                      <span class="tultip"
                                        ><i class="fa fa-sort-desc"></i>ADD TO
                                        CART</span
                                      ><a href="#"
                                        ><span class="icon-icon-32846"></span
                                      ></a>
                                    </li>
                                    <li>
                                      <a href="#"
                                        ><span class="fa fa-heart-o"></span
                                      ></a>
                                    </li>
                                  </ul>
                                </div>
                                <div class="bottom-content">
                                  <h4><a href="#">It Contains:</a></h4>
                                  <p>
                                    35% of organic raisins 55% of oats and 10%
                                    of butter.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div> -->
                    <!-- End of .row -->
                  </div>
                  <!-- End of .shop_page -->
                </div>
                <!-- End of .related_product -->
              </div>
              <!-- End of .wrapper -->
            </div>
            <!-- End of .col -->
          </div>
          <!-- End of .row -->
        </div>
        <!-- End of .container -->
      </div>
      <!-- End of .shop_single_page -->

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
            <li><a href="mailto:punjabgrocerhalalmeat@gmail.com" class="text-muted"><i class="fa fa-envelope me-2"></i>punjabgrocerhalalmeat@gmail.com</a></li>
            <li><a href="tel:+19054517666" class="text-muted"><i class="fa fa-phone me-2"></i>(905) 451‑7666</a></li>
            <li><a href="#" class="text-muted"><i class="fa fa-home me-2"></i>5 Montpelier St, Unit 106, 107, Brampton, ON L6Y 6H4</a></li>
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
