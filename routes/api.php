<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MovieController;
use App\Http\Controllers\API\ClaimController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CountriesController;
use App\Http\Controllers\API\StatesController;
use App\Http\Controllers\API\CitiesController;
use App\Http\Controllers\API\SetupController;


use App\Http\Controllers\RolesController;
use App\Http\Controllers\PermissionsController;

use App\Http\Controllers\API\QNAController;
use App\Http\Controllers\API\CampaignController;
use App\Http\Controllers\API\TagController;

use App\Http\Controllers\API\VehicleController;

use App\Http\Controllers\API\SliderController;
use App\Http\Controllers\API\TestimonialController;
use App\Http\Controllers\API\TestrideController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\EnquiryController;
use App\Http\Controllers\API\CompanyController;


use App\Http\Controllers\API\NavigationController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\OrderController;

use App\Http\Controllers\API\DeliveryController;

use App\Http\Controllers\API\BlogController;

use App\Http\Controllers\API\SocietyController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => ['api', 'cbsd']], function() {
    Route::post('/enquiry/create-update', [EnquiryController::class, 'createAndUpdateEnquiry'])->name('enquiry.create');
    Route::group(['prefix' => 'company'], function() {
        Route::get('/expose', [CompanyController::class, 'expose'])->name('company.expose');
    });
    
    Route::group(['prefix' => 'societymgmt'], function() {
        
        Route::get('/generate-bill', [SocietyController::class, 'generateBill'])->name('societymgmt.generateBill');
        Route::get('/generate-bill/{billmonth}', [SocietyController::class, 'generateBill'])->name('societymgmt.generateBillmonthwise');
        Route::get('/generate-billv2', [SocietyController::class, 'generateBillV2'])->name('societymgmt.generateBillV2');
        
        
    });
    Route::post('/user-exam-result-list', [CampaignController::class, 'ExamResultList']);
     /**
         * Login Routes
         */
         
        Route::post('/setup/lists', [SetupController::class, 'index'])->name('setup.lists');
        Route::post('/login', [UserController::class, 'login'])->name('login');
        Route::get('/get-file/{slug}', [HomeController::class, 'getFiles']);
        /**
         * Register Routes
         */
        Route::post('/register', [UserController::class, 'register']);
        /**
         * Logout Routes
         */
        
        Route::post('/logout', [UserController::class, 'logout']);
        /**
         * Claim Routes
         */
         
        /*Navigation Routes*/
        Route::post('/navigation', [NavigationController::class, 'index'])->name('navigation');
        Route::post('/upload-nav-excel', [NavigationController::class, 'uploadExcel'])->name('navigation.uploadExcel');
      
      
        /*Route::post('/accident_image_list', [ClaimController::class, 'accidentImageList']);
        Route::post('/inspection_detail_list', [ClaimController::class, 'inspectionDetailList']);
        Route::post('/claim', [ClaimController::class, 'create']);
        Route::post('/storeAccidentImages', [ClaimController::class, 'storeAccidentImages']);
        Route::post('/storeInspectionDetails', [ClaimController::class, 'storeInspectionDetails']);
        Route::post('/delete_claim', [ClaimController::class, 'destroyClaim']);
        Route::post('/delete_accident_image', [ClaimController::class, 'deleteAccidentImage']);
        Route::post('/delete_Inspection_detail', [ClaimController::class, 'deleteInspectionDetail']);*/
        
        Route::group(['prefix' => 'states' ], function() {
            Route::post('/create', [StatesController::class, 'create'])->name('states.create');
            Route::post('/list', [StatesController::class, 'list'])->name('states.list');
            Route::get('/view/{slug}', [StatesController::class, 'view'])->name('states.view');
            Route::post('/update/{slug}', [StatesController::class, 'edit'])->name('states.edit');
            Route::post('/delete', [StatesController::class, 'delete'])->name('states.delete');
        });
        
        Route::group(['prefix' => 'cities' ], function() {
            Route::post('/create', [CitiesController::class, 'create'])->name('cities.create');
            Route::post('/list', [CitiesController::class, 'list'])->name('cities.list');
            Route::get('/view/{slug}', [CitiesController::class, 'view'])->name('cities.view');
            Route::post('/update/{slug}', [CitiesController::class, 'edit'])->name('cities.edit');
            Route::post('/delete', [CitiesController::class, 'delete'])->name('cities.delete');
        });
        
        Route::group(['prefix' => 'countries' ], function() {
            Route::post('/create', [CountriesController::class, 'create'])->name('countries.create');
            Route::post('/list', [CountriesController::class, 'list'])->name('countries.list');
            Route::get('/view/{slug}', [CountriesController::class, 'view'])->name('countries.view');
            Route::post('/update/{slug}', [CountriesController::class, 'edit'])->name('countries.edit');
            Route::post('/delete', [CountriesController::class, 'delete'])->name('countries.delete');
        });
        
        Route::group(['prefix' => 'tags' ], function() {
            Route::post('/list', [TagController::class, 'index'])->name('tags.index');
            Route::post('/create', [TagController::class, 'create'])->name('tags.create');
            Route::post('/delete', [TagController::class, 'delete'])->name('tags.delete');
        });
        
        /*Website Related Routes starts*/
            Route::group(['prefix' => 'slider' ], function() {
                Route::post('/all', [SliderController::class, 'getSliders'])->name('slider');
                Route::post('/list', [SliderController::class, 'getSliderList'])->name('slider.list');
                Route::post('/show/{id}', [SliderController::class, 'getSliderAndDetails'])->name('slider.show');
            });
            
            Route::group(['prefix' => 'slider' ], function() {
            Route::post('/all', [SliderController::class, 'getSliders'])->name('slider');
            Route::post('/list', [SliderController::class, 'getSliderList'])->name('slider.list');
            Route::post('/create-update', [SliderController::class, 'createAndUpdateSlider'])->name('slider.create');
            Route::post('/delete/{id}', [SliderController::class, 'deleteSlider'])->name('slider.delete');
            Route::post('/detail/delete/{id}', [SliderController::class, 'deleteSliderDetail'])->name('slider.detail.delete');
            Route::post('/show/{id}', [SliderController::class, 'getadminSliderAndDetails'])->name('slider.adminshow');//this function for admin panel
        });
            Route::post('/service-list', [ProductController::class, 'getServices'])->name('product.services');
            Route::post('/product-list', [ProductController::class, 'getServices'])->name('product.products');
        /*website Routes ends*/
    });
    
    
Route::group(['middleware' => ['auth.role:admin,superadmin,posp,workshop','cbsd']], function() {
    
    /*setup route starts*/
    Route::group(['prefix' => 'setup' ], function() {
        Route::post('/list', [SetupController::class, 'index'])->name('setup.index');
        Route::post('/create', [SetupController::class, 'create'])->name('setup.create');
        Route::post('/modulewise-search', [SetupController::class, 'modulewisedata'])->name('setup.modulewisedata');
    
        Route::post('/createAboutus', [SetupController::class, 'createAboutus'])->name('setup.createAboutus');
    });
    
    Route::group(['prefix' => 'config' ], function() {
        Route::post('/module', [SetupController::class, 'loadConfig'])->name('module.config');
        
    });
    /*setup route ends*/

        Route::post('/add_user', [UserController::class, 'add_user']);

        /**
         * User Routes
         */
         
         
        Route::post('/upload-user-excel',  [UserController::class, 'uploadUserExcel']);
        Route::post('/export-lms-summary',  [UserController::class, 'exportLmsSummary']);
         Route::post('/reset-password',  [UserController::class, 'resetPassword']);
        Route::group(['prefix' => 'users' ], function() {
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/create', [UserController::class, 'store'])->name('users.store');
            Route::post('/list', [UserController::class, 'list'])->name('users.list');
            Route::get('/userRoles/{id}', [UserController::class, 'userRoles'])->name('users.userRoles');
            Route::post('/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
            Route::post('/assign-role/{id}', [UserController::class, 'userAssignRole'])->name('users.assign-role');
            Route::post('/delete-role/{id}', [UserController::class, 'userDeleteRole'])->name('users.delete.role');
            Route::post('/roles', [UserController::class, 'roleCreate'])->name('users.roleCreate');
            Route::post('/roles-list', [UserController::class, 'rolesList'])->name('users.rolesList');
            Route::post('/agent-workshop-list', [UserController::class, 'getAgentAndWorkshopList'])->name('users.getAgentAndWorkshopList');
            Route::post('/assign-claim', [UserController::class, 'createAssignClaim'])->name('users.createAssignClaim');
            Route::post('/update-assign-claim', [UserController::class, 'updateAssignClaim'])->name('users.updateAssignClaim');
            Route::post('/myprofile', [UserController::class, 'myprofiles'])->name('users.myprofiles');
            
            
            Route::post('/createTeam', [UserController::class, 'createTeam'])->name('users.createTeam');
            Route::post('/listTeam', [UserController::class, 'listTeam'])->name('users.listTeam');
            Route::post('/deleteTeam', [UserController::class, 'deleteTeam'])->name('users.deleteTeam');
            
            
            Route::post('/userCreate', [UserController::class, 'userStore'])->name('users.userStore');
        });
        
        
        
        Route::group(['prefix' => 'product' ], function() {

            Route::post('/product-service/{id}', [ProductController::class, 'getProductAndServices'])->name('product.service');
            Route::post('/create-product', [ProductController::class, 'createProduct'])->name('product.createProduct');
            Route::post('/list', [ProductController::class, 'index'])->name('product.index');
            Route::post('/delete-product', [ProductController::class, 'destroyProduct'])->name('product.destroyProduct');
            Route::post('/create-product-category', [ProductController::class, 'createProductCategory'])->name('product.createProductCategory');
            Route::post('/product-category-list', [ProductController::class, 'productCategoryList'])->name('product.productCategoryList');
            Route::post('/get-product-category', [ProductController::class, 'getProductCategory'])->name('product.getProductCategory');
            Route::post('/delete-product-category', [ProductController::class, 'destroyProductCategory'])->name('product.destroyProductCategory');
            Route::post('/product-service-list', [ProductController::class, 'getProductAndServiceList'])->name('product.productServiceLists');
            Route::post('/getProductCategoryDetail', [ProductController::class, 'getProductCategoryDetail'])->name('product.getProductCategoryDetail');
            
            Route::post('/brands', [ProductController::class, 'getBrandList'])->name('product.getBrandList');
            Route::post('/createBrand', [ProductController::class, 'createBrand'])->name('product.createBrand');
            Route::post('/deleteBrand', [ProductController::class, 'deleteBrand'])->name('product.deleteBrand');
        });
            

        Route::post('/get-campaign-by-reference-id', [CampaignController::class, 'getCampaignByReferenceID'])->name('get.campaign.by.reference.id');
        Route::post('/get-campaign-by-campaign-code', [CampaignController::class, 'getCampaignByCampaignCode'])->name('get.campaign.by.campaign.code');
        
        /*website routes starts*/
        Route::group(['prefix' => 'testimonial' ], function() {
            Route::post('/list', [TestimonialController::class, 'getTestimonialList'])->name('testimonial.list');
            Route::post('/create-update', [TestimonialController::class, 'createAndUpdateTestimonial'])->name('testimonial.create');
            Route::post('/delete/{id}', [TestimonialController::class, 'deleteTestimonial'])->name('testimonial.delete');
            Route::post('/show/{id}', [TestimonialController::class, 'getTestimonialDetail'])->name('testimonial.show');
        });
        
        
        Route::group(['prefix' => 'enquiry' ], function() {
            Route::post('/list', [EnquiryController::class, 'getEnquiryList'])->name('enquiry.list');
            Route::post('/existinglist', [EnquiryController::class, 'getExistingEnquiryList'])->name('enquiry.existinglist');
            
            Route::post('/delete/{id}', [EnquiryController::class, 'deleteEnquiry'])->name('enquiry.delete');
            Route::post('/show/{id}', [EnquiryController::class, 'getEnquiryDetail'])->name('enquiry.show');
            Route::post('/updateStatus', [EnquiryController::class, 'updateEnquiryStatus'])->name('enquiry.updateStatus');
        });
        
        Route::group(['prefix' => 'testride' ], function() {
            Route::post('/list', [TestrideController::class, 'getTestrideList'])->name('testride.list');
            Route::post('/create-update', [TestrideController::class, 'createAndUpdateTestride'])->name('testride.create');
            Route::post('/delete/{id}', [TestrideController::class, 'deleteTestride'])->name('testride.delete');
            Route::post('/show/{id}', [TestrideController::class, 'getTestrideDetail'])->name('testride.show');
            Route::post('/updateStatus', [TestrideController::class, 'updateTestrideStatus'])->name('testride.updateStatus');
        });
        
        Route::group(['prefix' => 'gallery' ], function() {
            Route::post('/lists', [GalleryController::class, 'index'])->name('gallery.lists');
            Route::post('/list', [GalleryController::class, 'getGalleryList'])->name('gallery.list');
            Route::post('/create', [GalleryController::class, 'createAndUpdateGallery'])->name('gallery.create');
            Route::post('/delete', [GalleryController::class, 'deleteGallery'])->name('gallery.delete');
        });
        
        Route::group(['prefix' => 'company'], function() {
            Route::get('/view/{slug}', [CompanyController::class, 'view'])->name('company.view');
            Route::get('/view', [CompanyController::class, 'view'])->name('company.getcurrentcompany');
            // Route::post('/update', [CompanyController::class, 'update'])->name('company.update');
            // Route::post('/create-update', [CompanyController::class, 'create_update'])->name('company.create_update');
            // Route::post('/delete', [CompanyController::class, 'delete'])->name('company.delete');
            Route::post('/list', [CompanyController::class, 'list'])->name('company.list');
            // Route::post('/create', [CompanyController::class, 'create'])->name('company.create');
            // Route::post('/assignUserCompanyRole', [CompanyController::class, 'assignUserCompanyRole'])->name('company.assignUserCompanyRole');
            
            Route::post('/create', [CompanyController::class, 'createUpdateCompany'])->name('company.create');
            Route::post('/types', [CompanyController::class, 'CompanyTypes'])->name('company.type');
        });
        
        /*website route ends*/
        Route::group(['prefix' => 'branch'], function() {
            Route::get('/', [CompanyBranchController::class, 'index'])->name('branch');
            Route::post('/create', [CompanyBranchController::class, 'create'])->name('branch.create');
            Route::post('/edit', [CompanyBranchController::class, 'edit'])->name('branch.edit');
            Route::post('/list', [CompanyBranchController::class, 'list'])->name('branch.list');
            Route::post('/delete', [CompanyBranchController::class, 'delete'])->name('branch.delete');
        });
        
        /*Blogs Route*/
        /*Route::group(['prefix' => 'blogs' ], function() {
            Route::post('/list', [BlogController::class, 'list'])->name('blog.list');
            Route::post('/view/{slug}', [BlogController::class, 'view'])->name('blog.view');
            Route::post('/create', [BlogController::class, 'create'])->name('blog.create');
            Route::post('/delete', [BlogController::class, 'delete'])->name('blog.delete');
            
            Route::post('/category-list', [BlogController::class, 'categoryList'])->name('blogcategory.list');
            //Route::post('/view/{slug}', [BlogController::class, 'categoryView'])->name('blogcategory.view');
            Route::post('/category-create', [BlogController::class, 'categoryCreate'])->name('blogcategory.create');
            Route::post('/category-delete', [BlogController::class, 'categoryDelete'])->name('blogcategory.delete');
        });*/
        
        /*vehicle routes starts*/
        Route::group(['prefix' => 'vehicle'], function() {
          
            Route::post('/list', [VehicleController::class, 'getVehicles'])->name('vehicle.list');
            Route::post('/get-vehicle-by-model/{slug}', [VehicleController::class, 'getVehicleByModel'])->name('vehicle.get.vehicle.by.model');
            Route::post('/export-vehicle-format', [VehicleController::class, 'exportVehicleUpload'])->name('vehicle.export.vehicle.format');
            Route::post('/import-excel', [VehicleController::class, 'importVehicleData'])->name('vehicle.import.vehicle');
            /*Upload docs*/
            Route::post('/upload-docs-vehicle', [VehicleController::class, 'UploadVehicleDocs']);
            
            Route::post('/create', [VehicleController::class, 'createVehicle'])->name('vehicle.create'); 
            Route::post('/delete/{id}', [VehicleController::class, 'deleteVehicle'])->name('vehicle.delete');
            Route::post('/get-vehicle-by-type', [VehicleController::class, 'getVehiclesByType'])->name('vehicle.get.vehicle.by.type');
            
            Route::get('/get-vehicle-make-model-type', [ProductController::class, 'getVehiclesMakeModelType'])->name('product.make.model.type');
        });
        Route::group(['prefix' => 'variation'], function() {
            
            Route::post('/list', [VehicleController::class, 'getVariationList'])->name('variation.list');
            Route::post('/create', [VehicleController::class, 'createVariation'])->name('variation.create');
            Route::post('/delete', [VehicleController::class, 'deleteVariation'])->name('variation.delete');
            
        });
        
        Route::group(['prefix' => 'feature'], function() {
            
            Route::post('/list', [VehicleController::class, 'getFeatureList'])->name('feature.list');
            Route::post('/create', [VehicleController::class, 'createFeature'])->name('feature.create');
            Route::post('/delete', [VehicleController::class, 'deleteFeature'])->name('feature.delete');
            
        });
        /*vehicle route ends*/
        
        /*invoice & quotation route starts*/
        Route::group(['prefix' => 'invoice'], function() {
            Route::post('/', [InvoiceController::class, 'index'])->name('invoice.index'); 
            Route::post('/create', [InvoiceController::class, 'create'])->name('invoice.create');
            Route::post('/detail', [InvoiceController::class, 'show'])->name('invoice.show');
            Route::post('/delete', [InvoiceController::class, 'delete'])->name('invoice.delete');
            Route::post('/report', [InvoiceController::class, 'downloadReport'])->name('invoice.report');
            Route::get('/report/{invoiceNo}', [InvoiceController::class, 'downloadReport2'])->name('invoice.report2');
               
        });
        
        Route::group(['prefix' => 'order'], function() {
            Route::post('/create', [OrderController::class, 'create'])->name('order.create');
        });
        
        Route::group(['prefix' => 'delivery'], function() {
            Route::get('/dashboard', [DeliveryController::class, 'dashboard'])->name('delivery.dashboard');
            Route::post('/list', [DeliveryController::class, 'getDeliveryList'])->name('delivery.delivery-list');
            Route::post('/cancel', [DeliveryController::class, 'cancel'])->name('delivery.cancel');
            Route::post('/confirm', [DeliveryController::class, 'confirm'])->name('delivery.confirm');
            Route::post('/cashbook-list', [DeliveryController::class, 'cashbookList'])->name('delivery.cashbook.list');
            Route::post('/deposit-cashbook', [DeliveryController::class, 'depositCashBook'])->name('delivery.deposit.cashbook');
            Route::post('/stock-summary', [DeliveryController::class, 'stockSummary'])->name('delivery.stock.summary');
            Route::post('/arealist', [DeliveryController::class, 'areaList'])->name('delivery.areaList');
        });
        /*invoice route ends*/
        
        /*Society mgmt routes*/
        Route::group(['prefix' => 'societymgmt'], function() {
            Route::post('/bill-list', [SocietyController::class, 'index'])->name('societymgmt.list');
            Route::group(['prefix' => 'flats'], function() {
                Route::post('/list', [SocietyController::class, 'flatList'])->name('flats.flatList');
            
            });
            
            Route::group(['prefix' => 'wings'], function() {
                Route::post('/list', [SocietyController::class, 'wingList'])->name('wings.wingList');
            
            });
            
            Route::post('/floors-wings', [SocietyController::class, 'getFloorsAndWings'])->name('societymgmt.floorsandwings');
            
            Route::get('/generate-pdf/{invoice_no}', [SocietyController::class, 'generatePdfBill'])->name('societymgmt.generatePdfBill');
            Route::get('/generate-multiple-pdf/{billmonth}', [SocietyController::class, 'generatePdfBillMultiple'])->name('societymgmt.generatePdfBillMultiple');
            Route::get('/generate-multiple-pdf', [SocietyController::class, 'generatePdfBillMultiple'])->name('societymgmt.generatePdfBillMultipleCurrentMonth');
            Route::get('/test-pdf', [SocietyController::class, 'testpdf'])->name('societymgmt.testpdf');
            Route::post('/bill/details', [SocietyController::class, 'view'])->name('societymgmt.bill-details');
            
        });
    });

    Route::group(['middleware' => ['auth.role:admin,superadmin,posp,workshop','cbsd']], function() {
        
        Route::post('/profile', [UserController::class, 'profile']);
        Route::post('/refresh', [UserController::class, 'refresh']);
        /*setup route starts*/
        Route::group(['prefix' => 'setup' ], function() {
            Route::post('/list', [SetupController::class, 'index'])->name('setup.index');
            Route::post('/create', [SetupController::class, 'create'])->name('setup.create');
            Route::post('/modulewise-search', [SetupController::class, 'modulewisedata'])->name('setup.modulewisedata');
        });
        /*setup route ends*/
   
   /**
     * User Routes
     */
     Route::group(['prefix' => 'users' ], function() {
        Route::post('/{type}', [UserController::class, 'index'])->name('users.index');
        Route::get('/show/{id}', [UserController::class, 'show'])->name('users.show');
    });
    
    
    /*website related route starts*/
    Route::group(['prefix' => 'slider' ], function() {
        Route::post('/all', [SliderController::class, 'getSliders'])->name('slider');
        Route::post('/sliderList', [SliderController::class, 'getSliderList'])->name('slider.sliderlist');
        Route::post('/create-update', [SliderController::class, 'createAndUpdateSlider'])->name('slider.create');
        Route::post('/delete/{id}', [SliderController::class, 'deleteSlider'])->name('slider.delete');
        Route::post('/detail/delete/{id}', [SliderController::class, 'deleteSliderDetail'])->name('slider.detail.delete');
        Route::post('/show/{id}', [SliderController::class, 'getSliderAndDetails'])->name('slider.show');
    });
    /*website route ends*/
    
    /** Product Routes **/
    Route::post('/create-setup', [ProductController::class, 'createSetup']);
    Route::post('/setup-list', [ProductController::class, 'setupList']);
    Route::post('/delete-setup', [ProductController::class, 'destroySetup']);
    Route::post('/get-module-list', [ProductController::class, 'getModuleList']);
        
    Route::post('/dashboard-list', [CampaignController::class, 'getDashboardList']);    
    /** Claim Routes **/
    /*Route::post('/dashboard-list', [ClaimController::class, 'getDashboardList']);
    Route::post('/claim-assessment', [ClaimController::class, 'claimAssessment']);
    Route::post('/claim_list', [ClaimController::class, 'index']);
    Route::post('/get-claim-details', [ClaimController::class, 'getClaimData']);
    Route::post('/add-images-to-claim-question', [ClaimController::class, 'AddImagesToClaimQuestion']);
    Route::post('/update-assessment-detail-by-id', [ClaimController::class, 'updateAssessmentDetailById']);
    Route::post('/get-assessment-detail-product', [ClaimController::class, 'getAssessmentDetailProduct']);
    Route::post('/add-assessment-image', [ClaimController::class, 'addAssessmentImage']);*/

    /*Online Examination route starts*/
    Route::post('get-question', [QNAController::class, 'getQuestion'])->name('get-question');
    Route::post('create-campaign', [QNAController::class, 'createCampaign'])->name('create-campaign');
    Route::post('create-question', [QNAController::class, 'createCampaignQuestion'])->name('create-question');
    Route::post('create-answer', [QNAController::class, 'createCampaignAnswers'])->name('create-answer');
    Route::post('question-answer-list', [QNAController::class, 'questionList'])->name('question-answer-list');
    Route::post('qna-ajax-list', [QNAController::class, 'qnaAjaxList'])->name('qna-ajax-list');
    Route::post('delete-question', [QNAController::class, 'deleteQuestion'])->name('delete-question');
    
    Route::post('campaign-list', [QNAController::class, 'CampaignList'])->name('campaign-list');
    
    /*Route::post('/assessment', [ClaimController::class, 'Assessment']);
    
    Route::post('/get-assessment-details', [ClaimController::class, 'getAssessmentDetails']);
    Route::post('/get-assessment-details-new', [ClaimController::class, 'getAssessmentDetailsNew']);*/
    
    
    Route::post('/get-campaign', [CampaignController::class, 'getCampaign']);
    Route::post('/get-campaign2', [CampaignController::class, 'getCampaign2']);
    Route::post('/create-user-exam-result', [CampaignController::class, 'createUserExamResult']);
    Route::post('/submit-answers', [CampaignController::class, 'submitAnswer']);
    
    
    /* Upload Excel */
    Route::post('/upload-excel-campaign', [CampaignController::class, 'uploadExcelCampaign']);
    
    Route::post('/examlist', [CampaignController::class, 'UserExamResultList']);
    
    Route::post('/assign-campaign', [QNAController::class, 'assignCampaign']);
    
    Route::post('/user-list-campaign', [QNAController::class, 'userListForCampaign']);
    
    Route::get('/download-courses-excel', [CampaignController::class, 'downloadCourses']);
    
    Route::get('/download-exams-excel', [CampaignController::class, 'downloadExamsExcel']);
    
    /*Upload docs*/
    
    Route::post('/upload-docs-campaign', [CampaignController::class, 'uploadDocsCampaign']);
    
    Route::post('/userexamlist', [CampaignController::class, 'UserExamList']);
    Route::post('/userexamdetail', [CampaignController::class, 'UserExamCampaignList']);
    
    Route::post('/upload-excel-campaign-update', [CampaignController::class, 'uploadExcelCampaignUpdate']);
    Route::post('/dashboarDetails', [CampaignController::class, 'dashboardDetailsList']);
    /*examination route ends*/
    /*Blogs API*/
    /*Blogs Route*/
        Route::group(['prefix' => 'blogs' ], function() {
            Route::post('/list', [BlogController::class, 'list'])->name('blog.list');
            Route::post('/view/{slug}', [BlogController::class, 'view'])->name('blog.view');
            Route::post('/create', [BlogController::class, 'create'])->name('blog.create');
            Route::post('/delete', [BlogController::class, 'delete'])->name('blog.delete');
            
            Route::post('/category-list', [BlogController::class, 'categoryList'])->name('blogcategory.list');
            Route::post('/category-view/{slug}', [BlogController::class, 'categoryView'])->name('blogcategory.view');
            Route::post('/category-blog-view/{slug}', [BlogController::class, 'blogCategoryView'])->name('blogcategory.blogCategoryView');
            Route::post('/category-create', [BlogController::class, 'categoryCreate'])->name('blogcategory.create');
            Route::post('/category-delete', [BlogController::class, 'categoryDelete'])->name('blogcategory.delete');
            
            Route::post('/post-list', [BlogController::class, 'blogPostList'])->name('blogPost.list');
            Route::post('/post-view/{slug}', [BlogController::class, 'blogPostView'])->name('blogPost.view');
            Route::post('/post-create', [BlogController::class, 'blogPostCreate'])->name('blogPost.create');
            Route::post('/post-delete', [BlogController::class, 'blogPostDelete'])->name('blogPost.delete');
        });
});


