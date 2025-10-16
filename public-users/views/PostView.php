<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<link rel="stylesheet" href="./assets/css/postview.css">

<div class="d-flex post-root" style="height: calc(100vh - 70px); overflow: hidden;">
    <!-- LEFT SIDE: MEDIA -->
    <div class="flex-grow-1 d-flex align-items-center justify-content-center bg-black post-media">
        <div id="postMediaCarousel" class="carousel slide w-100 h-100" data-bs-ride="false">
            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100 d-flex align-items-center justify-content-center bg-black">
                    <video controls class="video-media">
                        <source src="./path/to/video.mp4" type="video/mp4">
                    </video>
                </div>
                <div class="carousel-item h-100 d-flex align-items-center justify-content-center bg-black">
                    <img src="./path/to/image.jpg" class="img-fluid image-media" alt="Post image">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#postMediaCarousel"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#postMediaCarousel"
                data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- RIGHT SIDE: POST DETAILS -->
    <div class="d-flex flex-column bg-white border-start post-side"
        style="width: 420px; max-width: 100%; height: calc(100vh - 70px);">

        <!-- Header -->
        <div class="p-3 border-bottom d-flex align-items-center">
            <img src="https://via.placeholder.com/40" alt="Profile" class="rounded-circle me-2"
                style="width: 40px; height: 40px;">
            <div>
                <h6 class="mb-0 fw-bold">HR Troy</h6>
                <small class="text-muted">October 6 at 4:42 PM · <i class="ti ti-world"></i></small>
            </div>
            <i class="ti ti-dots ms-auto"></i>
        </div>

        <!-- Caption -->
        <div class="p-3 border-bottom" id="captionContainer">
            <div id="captionText" class="small position-relative" style="max-height: 80px; overflow: hidden;">
                <p class="text-danger fw-bold mb-1">!!!NOW HIRING: MARKETING ASSISTANT</p>
                <p class="text-primary fw-bold mb-1">&gt; MALE APPLICANTS &lt;</p>
                <p class="fw-bold mb-1">Branches Hiring:</p>
                <p class="fw-bold mb-0">REGION 9</p>
                <p class="ms-3 mb-0">MotorTrade ZAMBO 1 VETERANS</p>
                <p class="fw-bold mt-2 mb-0">REGION 10</p>
                <p class="ms-3 mb-0">MotorTrade CDO 7 TIANO</p>
                <p class="ms-3 mb-0">MotorTrade CDO 6 RAULANG</p>
                <p class="ms-3 mb-0">MotorTrade CDO 4 COGON</p>
                <p class="mt-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at enim nec lacus
                    tempor commodo. Quisque suscipit eu justo sit amet convallis. Nulla facilisi. Curabitur
                    vitae porta felis. Vivamus ac ligula non mi euismod feugiat. Duis quis eros ac urna
                    tristique luctus. Nunc vitae justo sit amet mi euismod laoreet at sed sapien.</p>
                <p class="small text-muted mt-2">with Rocky Advincula and 17 others.</p>

                <!-- Fade overlay -->
                <div id="fadeOverlay"
                    style="position:absolute; bottom:0; left:0; right:0; height:40px; background: linear-gradient(transparent, white); display:block; pointer-events: none; z-index:2;">
                </div>

            </div>

            <!-- See more / See less button placed outside the collapsed area so it's always visible -->
            <a href="javascript:void(0)" id="seeMoreBtn" class="text-decoration-none small fw-bold d-block mt-2"
                style="position:relative; z-index:3; color:#0d6efd;">See more</a>
        </div>

        <!-- Comments -->
        <div class="px-3 py-2 flex-grow-1 overflow-auto comments-scroll">
            <?php
      $comments = [
        ['profile' => 'Dan P Pabaloi', 'text' => 'How to apply sir'],
        ['profile' => 'Ashrid Ashrid', 'text' => 'I’m interested'],
        ['profile' => 'Jandy Whyte Razo', 'text' => 'Sir aha na nga Quezon buldicson ni??'],
        ['profile' => 'Liezls Janson', 'text' => 'Hello Sir Good day you.. done napo sending resume'],
      ];

      foreach ($comments as $comment) {
        echo '<div class="d-flex mb-3">';
        echo '<img src="https://via.placeholder.com/30" alt="' . htmlspecialchars($comment['profile']) . '" class="rounded-circle me-2" style="width:30px;height:30px;">';
        echo '<div><div class="bg-light rounded-3 px-2 py-1">';
        echo '<strong>' . htmlspecialchars($comment['profile']) . ':</strong> ' . htmlspecialchars($comment['text']);
        echo '</div><div class="ms-2"><a href="#" class="small text-muted me-2 text-decoration-none">Like</a><a href="#" class="small text-muted text-decoration-none">Reply</a></div></div>';
        echo '</div>';
      }
      ?>
        </div>

        <!-- Comment Input -->
        <div class="border-top p-3">
            <form method="post" action="../controllers/comment-controller.php">
                <div class="d-flex align-items-center">
                    <img src="https://via.placeholder.com/30" class="rounded-circle me-2"
                        style="width:30px;height:30px;">
                    <input type="text" name="comment_text"
                        class="form-control form-control-sm rounded-pill bg-light border-0"
                        placeholder="Write a comment..." required>
                </div>
                <input type="hidden" name="post_id" value="[JOB_POST_ID]">
            </form>
        </div>

    </div>
</div>

<script src="assets/js/postview.js"></script>

<?php include __DIR__ . '/../include/footer.php'; ?>