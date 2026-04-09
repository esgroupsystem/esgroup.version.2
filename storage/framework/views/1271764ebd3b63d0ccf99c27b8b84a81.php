<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row min-vh-100 flex-center g-0">
        <div class="col-lg-8 col-xxl-5 py-3 position-relative">
            <img class="bg-auth-circle-shape" src="<?php echo e(asset('assets/img/icons/spot-illustrations/bg-shape.png')); ?>" alt="" width="250">
            <img class="bg-auth-circle-shape-2" src="<?php echo e(asset('assets/img/icons/spot-illustrations/shape-1.png')); ?>" alt="" width="150">

            <div class="card overflow-hidden z-1">
                <div class="card-body p-0">
                    <div class="row g-0 h-100">

                        
                        <div class="col-md-5 text-center bg-card-gradient">
                            <div class="position-relative p-4 pt-md-5 pb-md-7" data-bs-theme="light">
                                <div class="bg-holder bg-auth-card-shape"
                                     style="background-image:url(<?php echo e(asset('assets/img/icons/spot-illustrations/half-circle.png')); ?>);">
                                </div>

                                <div class="z-1 position-relative">
                                    <span class="link-light mb-4 font-sans-serif fs-5 d-inline-block fw-bolder">
                                        ES GROUP
                                    </span>
                                    <p class="opacity-75 text-white">
                                        Secure login for employees and administrators.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 mb-4 mt-md-4 mb-md-5" data-bs-theme="light">
                                <p class="text-white">
                                    Don’t have an account?<br>
                                    <a class="text-decoration-underline link-light" href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                        Register now
                                    </a>
                                </p>

                                <p class="mb-0 mt-4 mt-md-5 fs-10 fw-semi-bold text-white opacity-75">
                                    By logging in, you agree to our
                                    <a class="text-decoration-underline text-white" href="#">Terms</a> &
                                    <a class="text-decoration-underline text-white" href="#">Privacy Policy</a>
                                </p>
                            </div>
                        </div>

                        
                        <div class="col-md-7 d-flex flex-center">
                            <div class="p-4 p-md-5 flex-grow-1">
                                <div class="row flex-between-center">
                                    <div class="col-auto">
                                        <h3>Account Login</h3>
                                    </div>
                                </div>

                                
                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger small">
                                        <ul class="mb-0">
                                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($error); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="<?php echo e(route('login.post')); ?>">
                                    <?php echo csrf_field(); ?>

                                    
                                    <div class="mb-3">
                                        <label class="form-label" for="username">Username</label>
                                        <input class="form-control"
                                               id="username"
                                               name="username"
                                               type="text"
                                               value="<?php echo e(old('username')); ?>"
                                               placeholder="Enter your username"
                                               required />
                                    </div>

                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="password">Password</label>
                                        </div>
                                        <input class="form-control"
                                               id="password"
                                               name="password"
                                               type="password"
                                               placeholder="Enter your password"
                                               required />
                                    </div>

                                    
                                    <div class="row flex-between-center">
                                        <div class="col-auto">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="remember"
                                                       id="remember" />
                                                <label class="form-check-label mb-0" for="remember">
                                                    Remember me
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <a class="fs-10" href="#">
                                                Forgot Password?
                                            </a>
                                        </div>
                                    </div>

                                    
                                    <div class="mb-3">
                                        <button class="btn btn-primary d-block w-100 mt-3" type="submit">
                                            Log in
                                        </button>
                                    </div>
                                </form>

                                
                                <div class="position-relative mt-4">
                                    <hr />
                                    <div class="divider-content-center">or log in with</div>
                                </div>

                                <div class="row g-2 mt-2">
                                    <div class="col-sm-6">
                                        <a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#">
                                            <span class="fab fa-google-plus-g me-2"></span> Google
                                        </a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a class="btn btn-outline-facebook btn-sm d-block w-100" href="#">
                                            <span class="fab fa-facebook-square me-2"></span> Facebook
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/landing/login.blade.php ENDPATH**/ ?>