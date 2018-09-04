<?php
require '../../../includes/header_start.php';

$id = sanitizeInput($_REQUEST['id']);


?>

<link rel="stylesheet" type="text/css" href="/html/crm/css/crm.min.css?v=<?php echo VERSION; ?>" />

<div class="col-md-10">
  <div class="card-box">
    <div class="row">
      <h1 class="page-header">View Company</h1>

      <div class="col-md-2">
          <div class="col-md-12 inner-box">
            <h2 class="inner-box-header m-b-10">Company Information</h2>

            <h3>SMCM, Inc</h3>
            <h4>(828) 966-9000</h4>

            <p>
              206 Vista Blvd<br />
              Arden, NC 28704
            </p>

            <p><a href="#" id="company-email" data-type="text" data-pk="1" data-title="Email Address" class="editable editable-click" style="">orders@smcm.us</a></p>


          </div>

          <div class="col-md-12 inner-box">
            <h2 class="inner-box-header m-b-10">Contacts</h2>

            <div class="contact-card">
              <h5><a href="#">John Smith</a></h5>

              <p>
                (828) 966-9000<br />
                jsmith@smcm.us
              </p>
            </div>

            <div class="contact-card">
              <h5><a href="#">Jane Doe</a></h5>

              <p>
                (828) 966-9000<br />
                jdoe@smcm.us
              </p>
            </div>
          </div>
      </div>

      <div class="col-md-10">
        <div class="row" style="min-height:220px;">
          <div class="col-md-8">
            <ul class="nav nav-tabs" id="companySummary" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-sticky-note-o m-r-5"></i> New Note</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile"><i class="fa fa-envelope-o m-r-5"></i> Email</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#dropdown1" role="tab" aria-controls="profile"><i class="fa fa-plus m-r-5"></i> Log Activity</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#dropdown2" role="tab" aria-controls="profile"><i class="fa fa-calendar m-r-5"></i> Schedule</a>
              </li>
            </ul>
            <div class="tab-content" id="companySummaryContent">
              <div role="tabpanel" class="tab-pane fade in active show" id="home" aria-labelledby="home-tab">
                <div id="new_note" style="width:100%;height:200px;border:solid #CCC;border-width:0 1px 1px 1px;"></div>

                <div class="new_note_actions">
                  <h6>Associated With</h6>

                  <div class="crm_contact_assc_list">
                    <div class="crm_contact_association">
                      <i class="icon-user"></i> <a href="#">John Smith</a>
                    </div>

                    <div class="crm_contact_association">
                      <i class="icon-user"></i> <a href="#">Jane Doe</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla
                  single-origin coffee squid. Exercitation +1 labore velit, blog sartorial
                  PBR leggings next level wes anderson artisan four loko farm-to-table
                  craft beer twee. Qui photo booth letterpress, commodo enim craft beer
                  mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                  organic, assumenda labore aesthetic magna delectus mollit. Keytar
                  helvetica VHS salvia yr, vero magna velit sapiente labore stumptown.
                  Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts
                  beard ut DIY ethical culpa terry richardson biodiesel. Art party
                  scenester stumptown, tumblr butcher vero sint qui sapiente accusamus
                  tattooed echo park.</p>
              </div>
              <div class="tab-pane fade" id="dropdown1" role="tabpanel" aria-labelledby="dropdown1-tab">
                <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out
                  mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.
                  Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard
                  locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR
                  banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg
                  banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy
                  retro mlkshk vice blog. Scenester cred you probably haven't heard of
                  them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth
                  chambray yr.</p>
              </div>
              <div class="tab-pane fade" id="dropdown2" role="tabpanel" aria-labelledby="dropdown2-tab">
                <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party
                  before they sold out master cleanse gluten-free squid scenester freegan
                  cosby sweater. Fanny pack portland seitan DIY, art party locavore wolf
                  cliche high life echo park Austin. Cred vinyl keffiyeh DIY salvia PBR,
                  banh mi before they sold out farm-to-table VHS viral locavore cosby
                  sweater. Lomo wolf viral, mustache readymade thundercats keffiyeh craft
                  beer marfa ethical. Wolf salvia freegan, sartorial keffiyeh echo park
                  vegan.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <ul class="nav nav-pills m-b-5 m-t-10" id="myTabalt" role="tablist">
              <li class="nav-item">
                <a class="nav-link active show" id="home-tab1" data-toggle="tab" href="#timeline" role="tab" aria-controls="home" aria-expanded="true" aria-selected="true"><i class="fa fa-long-arrow-down m-r-5"></i> Timeline</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab1" data-toggle="tab" href="#profile1" role="tab" aria-controls="profile" aria-selected="false"><i class="fa fa-clone m-r-5"></i> Notes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab1" data-toggle="tab" href="#dropdown1-alt" role="tab" aria-controls="profile" aria-selected="false"><i class="fa fa-envelope-o m-r-5"></i> Email History</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab1" data-toggle="tab" href="#dropdown2-alt" role="tab" aria-controls="profile" aria-selected="false"><i class="fa fa-comments-o m-r-5"></i> Activity Log</a>
              </li>
            </ul>
            <div class="tab-content" id="myTabaltContent">
              <div role="tabpanel" class="tab-pane fade in active show" id="timeline" aria-labelledby="home-tab">
                <div class="timeline">
                  <article class="timeline-item alt">
                    <div class="text-right">
                      <div class="time-show first">
                        <a href="#" class="btn btn-custom w-lg">Today</a>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item alt">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow-alt"></span>
                          <span class="timeline-icon bg-danger"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-danger">1 hour ago</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Dolorum provident rerum aut hic quasi placeat iure tempora laudantium ipsa ad debitis unde? </p>
                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item ">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow"></span>
                          <span class="timeline-icon bg-success"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-success">2 hours ago</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>consectetur adipisicing elit. Iusto, optio, dolorum <a href="#">John deon</a> provident rerum aut hic quasi placeat iure tempora laudantium </p>

                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item alt">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow-alt"></span>
                          <span class="timeline-icon bg-primary"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-primary">10 hours ago</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>3 new photo Uploaded on facebook fan page</p>
                          <div class="album">
                            <a href="#">
                              <img alt="" src="assets/images/gallery/1.jpg">
                            </a>
                            <a href="#">
                              <img alt="" src="assets/images/gallery/2.jpg">
                            </a>
                            <a href="#">
                              <img alt="" src="assets/images/gallery/3.jpg">
                            </a>
                          </div>
                          <div class="clearfix"></div>
                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow"></span>
                          <span class="timeline-icon bg-purple"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-purple">14 hours ago</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Outdoor visit at California State Route 85 with John Boltana &amp;
                            Harry Piterson regarding to setup a new show room.</p>
                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item alt">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow-alt"></span>
                          <span class="timeline-icon"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-muted">19 hours ago</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Jonatha Smith added new milestone <span><a href="#">Pathek</a></span>
                            Lorem ipsum dolor sit amet consiquest dio</p>
                        </div>
                      </div>
                    </div>
                  </article>

                  <article class="timeline-item alt">
                    <div class="text-right">
                      <div class="time-show">
                        <a href="#" class="btn btn-custom w-lg">Yesterday</a>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow"></span>
                          <span class="timeline-icon bg-warning"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-warning">07 January 2016</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Montly Regular Medical check up at Greenland Hospital by the
                            doctor <span><a href="#"> Johm meon </a></span>
                          </p>

                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item alt">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow-alt"></span>
                          <span class="timeline-icon bg-primary"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-primary">07 January 2016</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Download the new updates of Ubold admin dashboard</p>
                        </div>
                      </div>
                    </div>
                  </article>

                  <article class="timeline-item">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow"></span>
                          <span class="timeline-icon bg-success"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-success">07 January 2016</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Jonatha Smith added new milestone <span><a class="blue" href="#">crishtian</a></span>
                            Lorem ipsum dolor sit amet consiquest dio</p>
                        </div>
                      </div>
                    </div>
                  </article>
                  <article class="timeline-item alt">
                    <div class="text-right">
                      <div class="time-show">
                        <a href="#" class="btn btn-custom w-lg">Last Month</a>
                      </div>
                    </div>
                  </article>

                  <article class="timeline-item alt">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow-alt"></span>
                          <span class="timeline-icon"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-muted">31 December 2015</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Download the new updates of Ubold admin dashboard</p>
                        </div>
                      </div>
                    </div>
                  </article>

                  <article class="timeline-item">
                    <div class="timeline-desk">
                      <div class="panel">
                        <div class="timeline-box">
                          <span class="arrow"></span>
                          <span class="timeline-icon bg-danger"><i class="zmdi zmdi-circle"></i></span>
                          <h4 class="text-danger">16 Decembar 2015</h4>
                          <p class="timeline-date text-muted"><small>08:25 am</small></p>
                          <p>Jonatha Smith added new milestone <span><a href="#">prank</a></span>
                            Lorem ipsum dolor sit amet consiquest dio</p>
                        </div>
                      </div>
                    </div>
                  </article>
                </div>
              </div>
              <div class="tab-pane fade" id="profile1" role="tabpanel" aria-labelledby="profile-tab">
                <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla
                  single-origin coffee squid. Exercitation +1 labore velit, blog sartorial
                  PBR leggings next level wes anderson artisan four loko farm-to-table
                  craft beer twee. Qui photo booth letterpress, commodo enim craft beer
                  mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                  organic, assumenda labore aesthetic magna delectus mollit. Keytar
                  helvetica VHS salvia yr, vero magna velit sapiente labore stumptown.
                  Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts
                  beard ut DIY ethical culpa terry richardson biodiesel. Art party
                  scenester stumptown, tumblr butcher vero sint qui sapiente accusamus
                  tattooed echo park.</p>
              </div>
              <div class="tab-pane fade" id="dropdown1-alt" role="tabpanel" aria-labelledby="dropdown1-tab">
                <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out
                  mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.
                  Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard
                  locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR
                  banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg
                  banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy
                  retro mlkshk vice blog. Scenester cred you probably haven't heard of
                  them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth
                  chambray yr.</p>
              </div>
              <div class="tab-pane fade" id="dropdown2-alt" role="tabpanel" aria-labelledby="dropdown2-tab">
                <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party
                  before they sold out master cleanse gluten-free squid scenester freegan
                  cosby sweater. Fanny pack portland seitan DIY, art party locavore wolf
                  cliche high life echo park Austin. Cred vinyl keffiyeh DIY salvia PBR,
                  banh mi before they sold out farm-to-table VHS viral locavore cosby
                  sweater. Lomo wolf viral, mustache readymade thundercats keffiyeh craft
                  beer marfa ethical. Wolf salvia freegan, sartorial keffiyeh echo park
                  vegan.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/companies.min.js"></script>

<script>
  $(function() {
    var myEditor;

    myEditor = new dhtmlXEditor({
      parent: "new_note",
      toolbar: true, // force dhtmlxToolbar using
      iconsPath: "/assets/plugins/dhtmlXEditor/imgs/", // path for toolbar icons
      content: ""
    });

    //modify buttons style
    $.fn.editableform.buttons =
      '<button type="submit" class="btn btn-primary editable-submit waves-effect waves-light"><i class="zmdi zmdi-check"></i></button>' +
      '<button type="button" class="btn editable-cancel btn-secondary waves-effect"><i class="zmdi zmdi-close"></i></button>';

    $(".company-email").editable({
      type: 'text',
      pk: 1,
      name: 'email',
      title: 'Enter email address',
      mode: 'inline'
    });
  });
</script>