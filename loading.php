<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
 <div id="loading" style="display: none" >
  <div>Add message here</div>
  <div>
  	Sas
    <img src="loading-gif-animation.gif" alt="" />
    <!-- <img src="icon-loader.gif" alt="" /> -->
  </div>
</div>
<button onclick="showLoader()" id="calculate_btn">Calculate </button>
<script type="text/javascript">
function showLoader(){
  $('#loading').css('display', 'block');
  $('submit_btn').disabled = true;
};


</script>