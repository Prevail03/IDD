<?php
function print_menu($data){
	$m = $data['menu-pos'];
	$s = $data['sub-menu-pos'];
	?>
	<nav class="navbar navbar-expand-sm navbar-dark bg-primary">
	  <a class="navbar-brand" href="./home.php"><?php echo(Settings::$system_name); ?></a>
	  <ul class="navbar-nav">
	    <li class="nav-item">
	      <a class="nav-link" href="../modules.php">Modules</a>
	    </li>
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 's')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Scheme Settings
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item" href="./unreg_scheme.php">Open Another Scheme</a>
	        <a class="dropdown-item <?php echo ($_SESSION['s1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's1')? "active" : ""; ?>" href="./scheme_settings.php">Basic Scheme Settings</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's2')? "active" : ""; ?>" href="./relief_allocation.php">Relief Allocation</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's3')? "active" : ""; ?>" href="./contribution_relief_allocation_priority.php">Contribution Relief Allocation Priority</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's4')? "active" : ""; ?>" href="./age_brackets.php">Age Brackets</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's5')? "active" : ""; ?>" href="./narrow_tax_bands.php">Narrow Tax Bands</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's6')? "active" : ""; ?>" href="./wide_tax_bands.php">Wide Tax Bands</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's7')? "active" : ""; ?>" href="./interest_calculation_mode.php">Interest Calculation Mode</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s8'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's8')? "active" : ""; ?>" href="./benefits_payment_portions.php">Benefits Payament Portions</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s9'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's9')? "active" : ""; ?>" href="./reasons_for_exit.php">Reasons for Exit</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s10'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's10')? "active" : ""; ?>" href="./scheme_periods.php">Scheme Periods</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s11'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's11')? "active" : ""; ?>" href="./movement_interest_rate.php">Movement Interest Rate</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s12'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's12')? "active" : ""; ?>" href="./scheme_reporting.php">Reporting</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['s13'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 's13')? "active" : ""; ?>" href="./scheme_logo.php">Logo</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'p')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Particulars
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($_SESSION['p1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p1')? "active" : ""; ?>" href="./particulars_members.php">Members</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['p8'] == 1)? "" : "d-none"; ?>-->
	        <!--<?php echo ($s === 'p8')? "active" : ""; ?>" href="./particulars_edit_member_vx.php">KYC Edit Request</a>-->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p2'] == 0)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p2')? "active" : ""; ?>" href="./particulars_pensioners.php">Pensioners</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p3')? "active" : ""; ?>" href="./particulars_ben_members.php">Beneficiaries - (Members)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p4')? "active" : ""; ?>" href="./particulars_ben_pensioners.php">Beneficiaries - (Pensioners)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p5')? "active" : ""; ?>" href="./particulars_service_providers.php">Service Providers Contacts</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p1'] == 0)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p1')? "active" : ""; ?>" href="./groups.php">Groups</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p6')? "active" : ""; ?>" href="./change_member_no.php">Change Member No.</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p6')? "active" : ""; ?>" href="./change_member_scheme_code.php">Change Member Scheme Code.</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['p8'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'p8')? "active" : ""; ?>" href="./particulars_merge_accounts.php">Merge Accounts</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy1')? "active" : ""; ?>" href="./particulars_check_if_member_numbers_exist.php">Check if Member Numbers Exist</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    



	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'i')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Interest Distribution
	      </a>
	      <div class="dropdown-menu">
	        <!--<a class="dropdown-item <?php echo ($_SESSION['i1'] == 0)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i1')? "active" : ""; ?>" href="./interest_add_dist1.php">Add Distribution</a>-->
	        <a class="dropdown-item <?php echo ($_SESSION['i1'] == 0)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i1')? "active" : ""; ?>" href="./tim_interest_add_dist1.php">Add Distribution</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['i2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i2')? "active" : ""; ?>" href="./interest_execute_dist.php">Execute Distribution</a>-->
	        <a class="dropdown-item <?php echo ($_SESSION['i2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i2')? "active" : ""; ?>" href="./tim_interest_execute_dist.php">Execute Distribution</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['i3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i3')? "active" : ""; ?>" href="./interest_undo_dist.php">Undo Distribution</a>-->
	
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['i4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i4')? "active" : ""; ?>" href="./interest_dist_allocations.php">Distribution Allocations</a>-->
	        <a class="dropdown-item <?php echo ($_SESSION['i4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i4')? "active" : ""; ?>" href="./tim_interest_dist_allocations.php">Distribution Allocations</a>
	        
	       <!-- <a class="dropdown-item <?php echo ($_SESSION['i5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i5')? "active" : ""; ?>" href="./interest_approximate1.php">Approximate Interest</a> -->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['i5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'i5')? "active" : ""; ?>" href="./tim_approximate_interest.php">Approximate Interest Rate</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['i5'] == 1)? "" : ""; ?> 
	        <?php echo ($s === 'i5')? "active" : ""; ?>" href="./tim_get_rate_from_value.php">Get Rate From Value</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'w')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Withdrawals
	      </a>
	      <div class="dropdown-menu">
	        <?php
	        //temporary access for celestine to do kirdi interest
	        if($_SESSION['user_id'] == 108 && $_SESSION['scheme_code'] == 'KE056')
	        {
	        ?>
	        

          <a class="dropdown-item" href="./new_claim.php">Process Withdrawal</a>
	        <?php 
	        } 
	        ?>
	        
	       
	          <?php
            $showWithdrawal = in_array($_SESSION['user_id'], [139905]) || ($_SESSION['w1'] == 1) ;

            if ($showWithdrawal): 
              ?>
             <a class="dropdown-item <?php echo ($_SESSION['w1'] == 1 ? '' : ''); ?> <?php echo ($s === 'w1') ? 'active' : ''; ?>" href="./new_claim.php">Process Withdrawal</a>
            <?php endif; ?>
	        
	        
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w1')? "active" : ""; ?>" href="./withdrawal_process1.php">Process Withdrawal - Old Method</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w8')? "active" : ""; ?>" href="./add_int_batch_withdrawals.php">Process Batch Additional Interest Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy16'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy16')? "active" : ""; ?>" href="./withdrawal_done_by_member.php">Member Initiated Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy2')? "active" : ""; ?>" href="./withdrawal_deleted.php">Deleted Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w2')? "active" : ""; ?>" href="./withdrawal_processed.php">Processed Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w3')? "active" : ""; ?>" href="./withdrawal_checked.php">Checked Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w4')? "active" : ""; ?>" href="./withdrawal_approved.php">Approved Withdrawals</a>
	        
	        <a class="dropdown-item" href="./withdrawal_reversed.php">Reversed Withdrawals</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy17'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy17')? "active" : ""; ?>" href="./withdrawal_reconcile_movement.php">Reconcile Withdrawal Member Movement Status</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy3')? "active" : ""; ?>" href="./withdrawal_adjust_movement_pattern.php">Adjust Movement Pattern</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w5')? "active" : ""; ?>" href="./withdrawal_processed_legacy.php">Processed Withdrawals <i>(Legacy)</i></a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w6')? "active" : ""; ?>" href="./withdrawal_checked_legacy.php">Checked Withdrawals <i>(Legacy)</i></a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['w7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'w7')? "active" : ""; ?>" href="./withdrawal_approved_legacy.php">Approved Withdrawals <i>(Legacy)</i></a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy4']== 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy4')? "active" : ""; ?>" href="./withdrawal_adjust_movement_pattern_legacy.php">Adjust Movement Pattern <i>(Legacy)</i></a>
	        
	        <?php
	        if($_SESSION['user_admin'] == 1){
	        ?>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy18'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy18')? "active" : ""; ?>" href="./import_withdrawals.php">Import Withdrawals</a>
	        
	        <?php
             }
	        ?>
	        
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'b')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Batch Entry
	      </a>
	      <div class="dropdown-menu">
	      
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b1')? "active" : ""; ?>" href="./batch_members1.php">Members</a>-->
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b2')? "active" : ""; ?>" href="./batch_pensioners1.php">Pensioners</a>-->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['b3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b3')? "active" : ""; ?>" href="./batch_mem_ben1.php">Beneficiaries - (Members)</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b4')? "active" : ""; ?>" href="./batch_pen_ben1.php">Beneficiaries - (Pensioners)</a>-->
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b5')? "active" : ""; ?>" href="./batch_closing_bal1.php">Closing Balances</a>-->
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b6')? "active" : ""; ?>" href="./batch_transfers_in1.php">Transfers In</a>-->
	        
	        <?php if($_SESSION['scheme_country']==='Uganda' OR $_SESSION['scheme_country'] ==='Zambia'){ ?>
	        <a class="dropdown-item <?php echo ($_SESSION['b7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b7')? "active" : ""; ?>" href="./batch_rem_cont1.php">Remitted Contributions</a>
	        <?php }?>
	        
	        <?php if($_SESSION['scheme_country']==='Uganda' OR $_SESSION['scheme_country'] ==='Zambia'){ ?>
	        <a class="dropdown-item <?php echo ($_SESSION['b8'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b8')? "active" : ""; ?>" href="./batch_unrem_cont1.php">Unremitted Contributions</a>
	        <?php }?>
	        
	        <?php
	        if($_SESSION['user_admin'] == 1){
	        ?>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy6')? "active" : ""; ?>" href="./batch_raw_cont.php">Raw Contributions</a>
	        
	        <?php
             }
	        ?>
	        
	        <?php if($_SESSION['scheme_country']==='Uganda' OR $_SESSION['scheme_country'] ==='Zambia'){ ?>
	        <a class="dropdown-item <?php echo ($_SESSION['b9'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b9')? "active" : ""; ?>" href="./batch_arrears1.php">Arrears</a>
	        <?php }?>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['b10'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b10')? "active" : ""; ?>" href="./batch_salaries1.php">Salaries</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b11'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b11')? "active" : ""; ?>" href="./batch_interest1.php">Interest</a>-->
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b17'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b17')? "active" : ""; ?>" href="./batch_withdrawals1.php">Withdrawals</a>-->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['b12'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b12')? "active" : ""; ?>" href="./view_batch_entries1.php">View Batch Entries</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['b13'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b13')? "active" : ""; ?>" href="./view_cum_entries1.php">View Cumulative Entries</a>
	        
	        <!--<a class="dropdown-item <?php echo ($_SESSION['b14'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b14')? "active" : ""; ?>" href="./delete_batch_by_id.php">Delete Batch</a>-->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['b15'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'b15')? "active" : ""; ?>" href="./convert_batch_unprep_prep1.php">Convert Unprepared Batch File To Prepared Batch File</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy7')? "active" : ""; ?>" href="./validate_cont1.php">Validate Contributions</a>
	        
	        <?php if (in_array($_SESSION['user_id'], [133165, 71706, 104547, 119499])): ?>
    <?php
        $isVisible = ($_SESSION['b16'] == 1);
        $isActive = ($s === 'b16');
        $class = 'dropdown-item';
        $class .= $isVisible ? '' : ' d-none';
        $class .= $isActive ? ' active' : '';
    ?>
    <a class="<?= $class ?>" href="./bulk_edit_members_details.php">Bulk Edit Members Details</a>
<?php endif; ?>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy5')? "active" : ""; ?>" href="./batch_get_new_entrants_members.php">Get New Entrants From Batch File (Members)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy8'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy8')? "active" : ""; ?>" href="./admin_accounts_recon.php">Admin-Accounts Reconciliations</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'y')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Payroll
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($s === 'y1')? "active" : ""; ?>" href="./idd_payroll.php">Process Payroll</a>
	        <a class="dropdown-item  <?php echo ($s === 'y4')? "active" : ""; ?>" href="./idd_payroll_batches.php">Payroll Batches</a>
	        <a class="dropdown-item  <?php echo ($s === 'y2')? "active" : ""; ?>" href="./idd_payroll_advance.php">Process Advance Request</a>
	        <a class="dropdown-item  <?php echo ($s === 'y3')? "active" : ""; ?>" href="./idd_payroll_actions.php">Payroll Actions</a>
            <a class="dropdown-item  <?php echo ($s === 'y4')? "active" : ""; ?>" href="./load_payroll.php">Load Payroll(Excel)</a>
	      </div>
	    </li>
	    
	    <!-- 
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'y')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Pensioners Payroll
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($_SESSION['y1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'y1')? "active" : ""; ?>" href="./pen_payrolls_processed.php">Processed Payrolls</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['y2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'y2')? "active" : ""; ?>" href="./pen_payrolls_checked.php">Checked Payrolls</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['y3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'y3')? "active" : ""; ?>" href="./pen_payrolls_approved.php">Approved Payrolls</a>
	      </div>
	    </li>
	    -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'r')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Reports
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($_SESSION['r1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r1')? "active" : ""; ?>" href="./report_trustees_and_hrs.php">Trustees & HRs</a>
	        
	         <a class="dropdown-item <?php echo ($_SESSION['xy9'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy9')? "active" : ""; ?>" href="./report_members1.php">Members (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r2')? "active" : ""; ?>" href="./report_member_statement1.php">Member Statement (PDF)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy10'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy10')? "active" : ""; ?>" href="./report_comp_member_statement1.php">Comprehensive Member Statement (PDF)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r3')? "active" : ""; ?>" href="./report_scheme_balances1.php">Scheme Balances (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r7')? "active" : ""; ?>" href="./report_custom_balances1.php">Custom Balances (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r4')? "active" : ""; ?>" href="./report_view_drawdown_bulk_statements.php">Bulk Members Statements</a>
	        
	        <!--
	        <a class="dropdown-item <?php echo ($_SESSION['r9'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r9')? "active" : ""; ?>" href="./report_task_scheduler.php">Task Scheduler: Bulk Member Statements</a>
	        -->
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r5')? "active" : ""; ?>" href="./report_withdrawals_benefits_payments.php">Posted Withdrawal Benefits Payments (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy11'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy11')? "active" : ""; ?>" href="./report_np_withdrawals_benefits_payments.php">Not Posted Withdrawal Benefits Payments (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r6')? "active" : ""; ?>" href="./report_age_profile_analysis.php">Age Profile Analysis (CSV)</a>
	        
	        <a class="dropdown-item <?php //echo ($_SESSION['xy12'] != 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy12')? "active" : ""; ?>" href="./report_batch_amounts_summary.php">Batch Amounts Summary</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r10'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r10')? "active" : ""; ?>" href="./report_monthly_cont_rem1.php">Monthly Contributions Remittances (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r11'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r11')? "active" : ""; ?>" href="./report_new_entrants_exits1.php">New Entrants & Exits (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r12'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r12')? "active" : ""; ?>" href="./report_reinstated1.php">Re-instated Members (CSV)</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy13'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy13')? "active" : ""; ?>" href="./report_withdrawals_benefits_payments_legacy.php">Posted Withdrawal Benefits Payments <i>(Legacy) (CSV)</i></a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy19'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy19')? "active" : ""; ?>" href="./report_np_withdrawals_benefits_payments_legacy.php">Not Posted Withdrawal Benefits Payments <i>(Legacy) (CSV)</i></a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['r20'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'r20')? "active" : ""; ?>" href="./report_view_bulk_statements_legacy.php">Bulk Members Statements <i>(Legacy)</i></a>
	        
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'c')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Compliance
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($_SESSION['c1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c1')? "active" : ""; ?>" href="./comp_scheme_activities.php">Scheme Activities</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c2')? "active" : ""; ?>" href="./comp_cont_rem.php">Contribution Remittance</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c3')? "active" : ""; ?>" href="./comp_reg_docs.php">Scheme Registration Documents</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c4')? "active" : ""; ?>" href="./comp_other_docs.php">Other Scheme Documents</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c5')? "active" : ""; ?>" href="./comp_trustee_board_composition.php">Trustee Board Composition</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c6')? "active" : ""; ?>" href="./comp_proposed_meeting_dates.php">Proposed Meeting Dates</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['c7'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'c7')? "active" : ""; ?>" href="./comp_service_providers.php">Service Providers</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'm')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Members Portal
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item <?php echo ($_SESSION['m1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm1')? "active" : ""; ?>" href="./portal_load_members.php">Load Members</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['m2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm2')? "active" : ""; ?>" href="./portal_view_members.php">View Members</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['xy14'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy14')? "active" : ""; ?>" href="./portal_view_login_requests.php">View Login Credentials Requests</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['m3'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm3')? "active" : ""; ?>" href="./portal_delete_batch.php">Delete Batch</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['m4'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm4')? "active" : ""; ?>" href="./portal_search_members.php">Search Members</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['m6'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm6')? "active" : ""; ?>" href="./portal_hide_periods.php">Hide Periods</a>
	        
	        <a class="dropdown-item <?php echo ($_SESSION['m5'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'm5')? "active" : ""; ?>" href="./portal_logs.php">Logs</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <!-- Start of Dropdown -->
	    <li class="nav-item dropdown">
	      <a class="nav-link dropdown-toggle <?php echo ($m === 'u')? "active" : ""; ?>" href="#" id="navbardrop" data-toggle="dropdown">
	        Utilities
	      </a>
	      <div class="dropdown-menu">
	        <a class="dropdown-item" href="../change_password.php">Change Password</a>
	        <a class="dropdown-item <?php echo ($_SESSION['u1'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'u1')? "active" : "./irr_bases.php"; ?>" href="./irr_bases.php">Retirement Projections</a>
	        <a class="dropdown-item <?php echo ($_SESSION['u2'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'u2')? "active" : "./templates.php"; ?>" href="./templates.php">Templates</a>
	        <a class="dropdown-item <?php echo ($_SESSION['xy15'] == 1)? "" : "d-none"; ?> 
	        <?php echo ($s === 'xy15')? "active" : "./templates.php"; ?>" href="./fix_contributions_dis_date.php">Accounts: Fix Contributions Display Date</a>
	      </div>
	    </li>
	    <!-- End of Dropdown -->
	    
	    <li class="nav-item">
	      <a class="nav-link" href="../logout.php">Logout</a>
	    </li>
	  </ul>
	</nav>
	<?php
}