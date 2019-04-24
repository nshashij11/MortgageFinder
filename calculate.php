<?php
if($_POST['mortgage_amount']){
	$inp_mortgagetype = $_POST['mortgage_type'];
	$inp_propertyvalue = $_POST['property_value'];
	$inp_mortgageamount = $_POST['mortgage_amount'];
	$inp_mortgageterms = $_POST['terms'];
	$inp_repaymentmethod = ($_POST['repayment_type'] != 'repayment') ? 'Interest Only with Repayment Vehicle' :'Repayment';
	$inp_dealsfixed = ($_POST['deals_fixed'] == 'fixed') ? 'true' : 'false';
	$inp_dealsvariable = ($_POST['deals_variable'] == 'variable') ? 'true' : 'false';
	$inp_initialperiod = $_POST['initial_period'];
	$inp_offset = (isset($_POST['deals_offset']) == 1) ? 'true' : 'false';
	$inp_noearlyrepayment = (isset($_POST['deals_no_repayment']) == 1) ? 'true' : 'false';
	$inp_subdeals = isset($_POST['sub_deals']) ? $_POST['sub_deals'] : '';
	
	$inp_purpose = ('Purchase' == $_POST['purpose']) ? 'Purchase' : 'Remortgage';
	$inp_type = ('Residential' == $_POST['type']) ? 'Residential' : 'buy-to-let';

	$inp_basicsalary = 38000;
	$inp_deposit = $inp_propertyvalue - $inp_mortgageamount;
	$loanamount = $inp_mortgageamount;
    if($loanamount <= 0){
        $loanamount = 0;
    }
    $ltv = round(($loanamount / $inp_propertyvalue) * 100);
    $results = '';
    $output =[];

	if (session_status() == PHP_SESSION_NONE) {//To avoid the notice
	    session_start();
	}
	//check the session response token exit - trigold life time is 2 hours. so let us take as 100 mins
	$cryptKey = md5('mortcalcsecutok');//security token from GetTokenResponse
	$cryptKeyexpire = md5('mortcalcsecutokexpire');//security token expire
	if(array_key_exists($cryptKeyexpire,$_SESSION) && !empty($_SESSION[$cryptKeyexpire])) {//check if its exist
	    //yes its exist, lets check the time diff
	    if(time() > $_SESSION[$cryptKeyexpire]){
	        unset($_SESSION[$cryptKey]);//unset security token
	        unset($_SESSION[$cryptKeyexpire]);//unset security token expire
	        header("Refresh:0");//reload the page
	        exit();
	    }
	}

	if(empty($_SESSION)){
		loginAPI($cryptKey, $cryptKeyexpire);	    
	}
	if(array_key_exists($cryptKey,$_SESSION) && !empty($_SESSION[$cryptKey]) && @$inp_mortgageamount > 0) {
		//get the CorrelationID by calling SourceProducts
    	$soapUrl = "https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx";
    	//For First Time Buyer
    	if('first_time_buyer' == $inp_mortgagetype){
    		$string = htmlentities('<SourceProductsRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/SourceProductsRequest">
				    <Version>4.0</Version>
				    <Header>
				        <CorrelationID xmlns="" />
				        <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
				    </Header>
				    <Data>
				        <DeliveryData>
				            <URL xmlns="" />
				            <TPID xmlns=""/>
				        </DeliveryData>
				        <SourcingData>
				            <Advisor xmlns="">
				                <CompanyFsaNumber></CompanyFsaNumber>
				                <Name></Name>
				                <NetworkName></NetworkName>
				                <UserID>12345</UserID>
				                <Panel>
				                    <Selector>
				                        <PanelGroup>100</PanelGroup>
				                        <PanelID>0</PanelID>
				                    </Selector>
				                </Panel>
				            </Advisor>
				            <CaseData xmlns="">
				                <Applicant>
				                    <ClientID>10280</ClientID>
				                    <Title>Mr</Title>
				                    <Firstname>Mortgage</Firstname>
				                    <Surname>Scout</Surname>
				                    <EmploymentAndIncome>
				                        <ContinuousEmployment />
				                        <CurrentPosition>
				                            <Years>10</Years>
				                        </CurrentPosition>
				                        <EmploymentType>Full Time Employee</EmploymentType>
				                        <BasicSalary>'.$inp_basicsalary.'</BasicSalary>
				                        <GrossAnnualIncome>'.$inp_basicsalary.'</GrossAnnualIncome>
				                    </EmploymentAndIncome>
				                    <ContactDetails>
				                        <HomeNumber>000000000</HomeNumber>
				                    </ContactDetails> 
				                    <LengthUKResidency></LengthUKResidency>                   
				                </Applicant>
				                <AdverseCredit>
				                    <AnyAdverseCredit>false</AnyAdverseCredit>
				                    <Arrears>false</Arrears>
				                    <Bankrupty>false</Bankrupty>
				                    <CCJ>false</CCJ>
				                    <Default>false</Default>
				                    <IVA>false</IVA>
				                    <Repossession>false</Repossession>
				                    </AdverseCredit>
				                <IncomeExpenditure>
				                    <MonthlyOutgoings>1090</MonthlyOutgoings>
				                    <TotalGrossAnnualIncome>'.$inp_basicsalary.'</TotalGrossAnnualIncome>
				                </IncomeExpenditure>
				            </CaseData>
				            <TotalToPayOptions xmlns="">
				                <CalculationTerm>'.$inp_mortgageterms.'</CalculationTerm>
				                <IncludeRemainingBalance>false</IncludeRemainingBalance>
				                <UseInitialPeriod>true</UseInitialPeriod>
				            </TotalToPayOptions>
				            <IncludeProducts xmlns="">
				                <EnhancedPanelOnly>false</EnhancedPanelOnly>
				                <Direct>false</Direct>
				                <Exclusives>true</Exclusives>
				                <Stepped>true</Stepped>
				                <Ineligible>false</Ineligible>
				                <Refer>true</Refer>
				                <OnlyVerifiedProducts>false</OnlyVerifiedProducts>
				            </IncludeProducts>
				            <PropertyToBeMortgaged xmlns="">
				                <PropertyValue>'.$inp_propertyvalue.'</PropertyValue>
				                <PropertyType>House</PropertyType>
				                <ResidenceType>Main Residence</ResidenceType>
				                <Location>England</Location>
				                <Tenure>Freehold</Tenure>
				                <ConstructionType>Standard</ConstructionType>
				                <NewBuild>'.(($inp_subdeals == 'NewBuild') ? 'true' : 'false').'</NewBuild>
				            </PropertyToBeMortgaged>
				            <Requirements xmlns="">
				                <FirstTimeBuyer>true</FirstTimeBuyer>
				                <Purpose>'.$inp_purpose.'</Purpose>                
				                <Type>'.$inp_type.'</Type>
				                <RepaymentMethod>'.$inp_repaymentmethod.'</RepaymentMethod>
				                <MortgageTerm>
				                    <Years>'.$inp_mortgageterms.'</Years>
				                    <Months>0</Months>
				                </MortgageTerm>
				                <Incentives>
				                    <ArrangementBookingFees>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </ArrangementBookingFees>
				                    <BorrowBackFacility>false</BorrowBackFacility>
				                    <Cashback>false</Cashback>
				                    <EarlyRepaymentFees>
				                      <NoRepaymentOverhang>false</NoRepaymentOverhang>
				                      <None>'.$inp_noearlyrepayment.'</None>
				                    </EarlyRepaymentFees>
				                    <HigherLendingCharge>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </HigherLendingCharge>
				                    <LegalFees>
				                      <PaidorFree>false</PaidorFree>
				                      <Refunded>false</Refunded>
				                    </LegalFees>
				                    <LinkedCurrentAccount>false</LinkedCurrentAccount>
				                    <NoCompulsoryBuildingInsurance>false</NoCompulsoryBuildingInsurance>
				                    <Overpayments>false</Overpayments>
				                    <PaymentHolidays>false</PaymentHolidays>
				                    <Portable>false</Portable>
				                    <Underpayments>false</Underpayments>
				                    <ValuationFees>
				                      <NoneOrFree>false</NoneOrFree>
				                      <Refunded>false</Refunded>
				                    </ValuationFees>
				                </Incentives>
				                <InitialPeriod>'.$inp_initialperiod.'</InitialPeriod>
				                <InterestRate/>
				                <InterestRateType>
				                    <Fixed>'.$inp_dealsfixed.'</Fixed>
				                    <Variable>'.$inp_dealsvariable.'</Variable>
				                    <Discounted>'.$inp_dealsvariable.'</Discounted>
				                    <Tracker>'.$inp_dealsvariable.'</Tracker>
				                    <Capped>'.$inp_dealsvariable.'</Capped>
				                    <Libor>'.$inp_dealsvariable.'</Libor>
				                    <Offset>'.$inp_offset.'</Offset>
				                </InterestRateType>
				                <Loan>
				                    <Deposit>'.$inp_deposit.'</Deposit>
				                    <LoanAmount>'.$loanamount.'</LoanAmount>
				                    <LTV>'.$ltv.'</LTV>
				                    <HelpToBuy>'.(($inp_subdeals == 'Help') ? 'true' : 'false').'</HelpToBuy>
				                    '.(($inp_subdeals == 'Shared') ? '<GovernmentScheme>Shared Ownership</GovernmentScheme><Share>50</Share><ShareToValue>90</ShareToValue>' : '').'
				                    <BuilderDeposit>false</BuilderDeposit>
				                    <VendorDeposit>false</VendorDeposit>
				                    <CapitalRestOptions>
				                      <Annually>false</Annually>
				                      <Daily>false</Daily>
				                      <Monthly>false</Monthly>
				                      <Quarterley>false</Quarterley>
				                    </CapitalRestOptions>
				                </Loan>
				            </Requirements>
				        </SourcingData>
				    </Data>
				</SourceProductsRequest>');
    	}else if('buy_to_let' == $inp_mortgagetype){
    		 $string = htmlentities('<SourceProductsRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/SourceProductsRequest">
		            <Version>4.0</Version>
		            <Header>
		                <CorrelationID xmlns="" />
		                <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
		            </Header>
		            <Data>
		                <SourcingData>
		                    <Advisor xmlns="">
		                        <CompanyFsaNumber>ABC123</CompanyFsaNumber>
		                        <Name>Mr User</Name>
		                        <NetworkName>Momentum</NetworkName>
		                        <!--UserID>1234</UserID-->
		                    </Advisor>
		                    <CaseData xmlns="">
		                        <Applicant>
		                            <!--ClientID>1</ClientID-->
		                            <Title>Mr</Title>
		                            <Firstname>Mortgage</Firstname>
		                            <Surname>Scout</Surname>
		                            <EmploymentAndIncome>
		                            <ContinuousEmployment></ContinuousEmployment>
		                            <CurrentPosition>
		                            <Years>'.$inp_mortgageterms.'</Years>
		                            </CurrentPosition>
		                            <EmploymentType>Full Time Employee</EmploymentType>
		                               <GrossAnnualIncome>'.$inp_basicsalary.'</GrossAnnualIncome>
		                            </EmploymentAndIncome>
		                            <ContactDetails>
		                                <!--HomeNumber>01926 621200</HomeNumber-->
		                            </ContactDetails>
		                            <LengthUKResidency></LengthUKResidency>
		                        </Applicant>
		                        <AdverseCredit>
		                        <AnyAdverseCredit>false</AnyAdverseCredit>
		                        <Arrears>false</Arrears>
		                        <Bankrupty>false</Bankrupty>
		                        <CCJ>false</CCJ>
		                        <Default>false</Default>
		                        <IVA>false</IVA>
		                        <Repossession>false</Repossession>
		                        </AdverseCredit>
		                        <IncomeExpenditure>
		                        <MonthlyOutgoings>1090</MonthlyOutgoings>
		                        <TotalGrossAnnualIncome>'.$inp_basicsalary.'</TotalGrossAnnualIncome>
		                        </IncomeExpenditure>
		                    </CaseData>
		                    <TotalToPayOptions xmlns="">
		                        <CalculationTerm>'.$inp_mortgageterms.'</CalculationTerm>
		                        <IncludeRemainingBalance>false</IncludeRemainingBalance>
		                        <UseInitialPeriod>false</UseInitialPeriod>
		                    </TotalToPayOptions>
		                    <IncludeProducts xmlns="">
		                        <Direct>false</Direct>
		                        <EnhancedPanelOnly>false</EnhancedPanelOnly>
		                        <Exclusives>true</Exclusives>
		                        <Ineligible>false</Ineligible>
		                        <OnlyVerifiedProducts>false</OnlyVerifiedProducts>
		                        <Refer>true</Refer>
		                        <Stepped>true</Stepped>
		                    </IncludeProducts>
		                    <PropertyToBeMortgaged xmlns="">
		                        <PropertyType>House</PropertyType>
		                        <PropertyValue>'.$inp_propertyvalue.'</PropertyValue>
		                        <ResidenceType>Other</ResidenceType>
		                    </PropertyToBeMortgaged>
		                    <Requirements xmlns="">
		                        <Purpose>'.$inp_purpose.'</Purpose>
		                        <FirstTimeBuyer>false</FirstTimeBuyer>
		                        <Type>Buy To Let</Type>
		                        <BuyToLet>
		                        <AllPartyAgreement>false</AllPartyAgreement>
		                        <ExpectedTenancyTerm/>
		                        <!--LettingBasis>
		                        <AssuredShortholdTenancy>false</AssuredShortholdTenancy>
		                        <Company>false</Company>
		                        <DSS>false</DSS>
		                        <HolidayLet>false</HolidayLet>
		                        <HousingAssociation>false</HousingAssociation>
		                        <LocalAuthority>false</LocalAuthority>
		                        <SittingTenant>false</SittingTenant>
		                        <Students>false</Students>
		                        </LettingBasis-->
		                        <MultipleOccupancy>false</MultipleOccupancy>
		                        <BTLType>'.(($inp_subdeals == 'Commercial') ? 'Business' : 'Business').'</BTLType>
		                        </BuyToLet>
		                        <RepaymentMethod>'.$inp_repaymentmethod.'</RepaymentMethod>
		                        <MortgageTerm>
		                            <Years>'.$inp_mortgageterms.'</Years>
		                            <Months>0</Months>
		                        </MortgageTerm>
		                        <Incentives>
		                            <ArrangementBookingFees>
		                              <AbilityToAddToLoan>false</AbilityToAddToLoan>
		                              <None>false</None>
		                            </ArrangementBookingFees>
		                            <BorrowBackFacility>false</BorrowBackFacility>
		                            <Cashback>false</Cashback>
		                            <EarlyRepaymentFees>
		                              <NoRepaymentOverhang>false</NoRepaymentOverhang>
		                              <None>'.$inp_noearlyrepayment.'</None>
		                            </EarlyRepaymentFees>
		                            <HigherLendingCharge>
		                              <AbilityToAddToLoan>false</AbilityToAddToLoan>
		                              <None>false</None>
		                            </HigherLendingCharge>
		                            <LegalFees>
		                              <PaidorFree>false</PaidorFree>
		                              <Refunded>false</Refunded>
		                            </LegalFees>
		                            <LinkedCurrentAccount>false</LinkedCurrentAccount>
		                            <NoCompulsoryBuildingInsurance>false</NoCompulsoryBuildingInsurance>
		                            <Overpayments>false</Overpayments>
		                            <PaymentHolidays>false</PaymentHolidays>
		                            <Portable>false</Portable>
		                            <Underpayments>false</Underpayments>
		                            <ValuationFees>
		                              <NoneOrFree>false</NoneOrFree>
		                              <Refunded>false</Refunded>
		                            </ValuationFees>
		                        </Incentives>
		                        <InitialPeriod>'.$inp_initialperiod.'</InitialPeriod>
		                        <InterestRateType>
		                            <Fixed>'.$inp_dealsfixed.'</Fixed>
		                            <Variable>'.$inp_dealsvariable.'</Variable>
		                            <Discounted>'.$inp_dealsvariable.'</Discounted>
		                            <Tracker>'.$inp_dealsvariable.'</Tracker>
		                            <Capped>'.$inp_dealsvariable.'</Capped>
		                            <Libor>'.$inp_dealsvariable.'</Libor>
		                            <Offset>'.$inp_offset.'</Offset>
		                        </InterestRateType>
		                        <Loan>
		                            <Deposit>'.$inp_deposit.'</Deposit>
		                            <LoanAmount>'.$loanamount.'</LoanAmount>
		                            <LTV>'.$ltv.'</LTV>
		                        </Loan>
		                    </Requirements>
		                </SourcingData>
		            </Data>
		        </SourceProductsRequest>');
    	}else if('remortgage' == $inp_mortgagetype){
    		$string = htmlentities('<SourceProductsRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/SourceProductsRequest">
				    <Version>4.0</Version>
				    <Header>
				        <CorrelationID xmlns="" />
				        <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
				    </Header>
				    <Data>
				        <DeliveryData>
				            <URL xmlns="" />
				            <TPID xmlns=""/>
				        </DeliveryData>
				        <SourcingData>
				            <Advisor xmlns="">
				                <CompanyFsaNumber></CompanyFsaNumber>
				                <Name></Name>
				                <NetworkName></NetworkName>
				                <UserID>12345</UserID>
				                <Panel>
				                    <Selector>
				                        <PanelGroup>100</PanelGroup>
				                        <PanelID>0</PanelID>
				                    </Selector>
				                </Panel>
				            </Advisor>
				            <CaseData xmlns="">
				                <Applicant>
				                    <ClientID>10280</ClientID>
				                    <Title>Mr</Title>
				                    <Firstname>Mortgage</Firstname>
				                    <Surname>Scout</Surname>
				                    <EmploymentAndIncome>
				                        <ContinuousEmployment />
				                        <CurrentPosition>
				                            <Years>10</Years>
				                        </CurrentPosition>
				                        <EmploymentType>Full Time Employee</EmploymentType>
				                        <BasicSalary>'.$inp_basicsalary.'</BasicSalary>
				                        <GrossAnnualIncome>'.$inp_basicsalary.'</GrossAnnualIncome>
				                    </EmploymentAndIncome>
				                    <ContactDetails>
				                        <HomeNumber>000000000</HomeNumber>
				                    </ContactDetails> 
				                    <LengthUKResidency></LengthUKResidency>                   
				                </Applicant>
				                <AdverseCredit>
				                    <AnyAdverseCredit>false</AnyAdverseCredit>
				                    <Arrears>false</Arrears>
				                    <Bankrupty>false</Bankrupty>
				                    <CCJ>false</CCJ>
				                    <Default>false</Default>
				                    <IVA>false</IVA>
				                    <Repossession>false</Repossession>
				                    </AdverseCredit>
				                <IncomeExpenditure>
				                    <MonthlyOutgoings>1090</MonthlyOutgoings>
				                    <TotalGrossAnnualIncome>'.$inp_basicsalary.'</TotalGrossAnnualIncome>
				                </IncomeExpenditure>
				            </CaseData>
				            <TotalToPayOptions xmlns="">
				                <CalculationTerm>'.$inp_mortgageterms.'</CalculationTerm>
				                <IncludeRemainingBalance>false</IncludeRemainingBalance>
				                <UseInitialPeriod>false</UseInitialPeriod>
				            </TotalToPayOptions>
				            <IncludeProducts xmlns="">
				                <EnhancedPanelOnly>false</EnhancedPanelOnly>
				                <Direct>false</Direct>
				                <Exclusives>true</Exclusives>
				                <Stepped>true</Stepped>
				                <Ineligible>false</Ineligible>
				                <Refer>true</Refer>
				                <OnlyVerifiedProducts>false</OnlyVerifiedProducts>
				            </IncludeProducts>
				            <PropertyToBeMortgaged xmlns="">
				                <PropertyValue>'.$inp_propertyvalue.'</PropertyValue>
				                <PropertyType>House</PropertyType>
				                <ResidenceType>Main Residence</ResidenceType>
				                <Location>England</Location>
				                <Tenure>Freehold</Tenure>
				                <ConstructionType>Standard</ConstructionType>
				                <NewBuild>false</NewBuild>
				            </PropertyToBeMortgaged>
				            <Requirements xmlns="">
				                <FirstTimeBuyer>false</FirstTimeBuyer>
				                <Purpose>'.$inp_purpose.'</Purpose>
				                <RemortgageReason>Standard Remortgage</RemortgageReason>             
				                <Type>'.$inp_type.'</Type>
				                <RepaymentMethod>'.$inp_repaymentmethod.'</RepaymentMethod>
				                <MortgageTerm>
				                    <Years>'.$inp_mortgageterms.'</Years>
				                    <Months>0</Months>
				                </MortgageTerm>
				                <Incentives>
				                    <ArrangementBookingFees>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </ArrangementBookingFees>
				                    <BorrowBackFacility>false</BorrowBackFacility>
				                    <Cashback>false</Cashback>
				                    <EarlyRepaymentFees>
				                      <NoRepaymentOverhang>false</NoRepaymentOverhang>
				                      <None>'.$inp_noearlyrepayment.'</None>
				                    </EarlyRepaymentFees>
				                    <HigherLendingCharge>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </HigherLendingCharge>
				                    <LegalFees>
				                      <PaidorFree>false</PaidorFree>
				                      <Refunded>false</Refunded>
				                    </LegalFees>
				                    <LinkedCurrentAccount>false</LinkedCurrentAccount>
				                    <NoCompulsoryBuildingInsurance>false</NoCompulsoryBuildingInsurance>
				                    <Overpayments>false</Overpayments>
				                    <PaymentHolidays>false</PaymentHolidays>
				                    <Portable>false</Portable>
				                    <Underpayments>false</Underpayments>
				                    <ValuationFees>
				                      <NoneOrFree>false</NoneOrFree>
				                      <Refunded>false</Refunded>
				                    </ValuationFees>
				                </Incentives>
				                <InitialPeriod>'.$inp_initialperiod.'</InitialPeriod>
				                <InterestRate/>
				                <InterestRateType>
				                    <Fixed>'.$inp_dealsfixed.'</Fixed>
				                    <Variable>'.$inp_dealsvariable.'</Variable>
				                    <Discounted>'.$inp_dealsvariable.'</Discounted>
				                    <Tracker>'.$inp_dealsvariable.'</Tracker>
				                    <Capped>'.$inp_dealsvariable.'</Capped>
				                    <Libor>'.$inp_dealsvariable.'</Libor>
				                    <Offset>'.$inp_offset.'</Offset>
				                </InterestRateType>
				                <Loan>
				                    <Deposit>'.$inp_deposit.'</Deposit>
				                    <LoanAmount>'.$loanamount.'</LoanAmount>
				                    <LTV>'.$ltv.'</LTV>
				                    <BuilderDeposit>false</BuilderDeposit>
				                    <VendorDeposit>false</VendorDeposit>
				                    <CapitalRestOptions>
				                      <Annually>false</Annually>
				                      <Daily>false</Daily>
				                      <Monthly>false</Monthly>
				                      <Quarterley>false</Quarterley>
				                    </CapitalRestOptions>
				                </Loan>
				            </Requirements>
				        </SourcingData>
				    </Data>
				</SourceProductsRequest>');
    	}else if('moving_home' == $inp_mortgagetype){
    		$string = htmlentities('<SourceProductsRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/SourceProductsRequest">
				    <Version>4.0</Version>
				    <Header>
				        <CorrelationID xmlns="" />
				        <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
				    </Header>
				    <Data>
				        <DeliveryData>
				            <URL xmlns="" />
				            <TPID xmlns=""/>
				        </DeliveryData>
				        <SourcingData>
				            <Advisor xmlns="">
				                <CompanyFsaNumber></CompanyFsaNumber>
				                <Name></Name>
				                <NetworkName></NetworkName>
				                <UserID>12345</UserID>
				                <Panel>
				                    <Selector>
				                        <PanelGroup>100</PanelGroup>
				                        <PanelID>0</PanelID>
				                    </Selector>
				                </Panel>
				            </Advisor>
				            <CaseData xmlns="">
				                <Applicant>
				                    <ClientID>10280</ClientID>
				                    <Title>Mr</Title>
				                    <Firstname>Mortgage</Firstname>
				                    <Surname>Scout</Surname>
				                    <EmploymentAndIncome>
				                        <ContinuousEmployment />
				                        <CurrentPosition>
				                            <Years>10</Years>
				                        </CurrentPosition>
				                        <EmploymentType>Full Time Employee</EmploymentType>
				                        <BasicSalary>'.$inp_basicsalary.'</BasicSalary>
				                        <GrossAnnualIncome>'.$inp_basicsalary.'</GrossAnnualIncome>
				                    </EmploymentAndIncome>
				                    <ContactDetails>
				                        <HomeNumber>000000000</HomeNumber>
				                    </ContactDetails> 
				                    <LengthUKResidency></LengthUKResidency>                   
				                </Applicant>
				                <AdverseCredit>
				                    <AnyAdverseCredit>false</AnyAdverseCredit>
				                    <Arrears>false</Arrears>
				                    <Bankrupty>false</Bankrupty>
				                    <CCJ>false</CCJ>
				                    <Default>false</Default>
				                    <IVA>false</IVA>
				                    <Repossession>false</Repossession>
				                    </AdverseCredit>
				                <IncomeExpenditure>
				                    <MonthlyOutgoings>1090</MonthlyOutgoings>
				                    <TotalGrossAnnualIncome>'.$inp_basicsalary.'</TotalGrossAnnualIncome>
				                </IncomeExpenditure>
				            </CaseData>
				            <TotalToPayOptions xmlns="">
				                <CalculationTerm>'.$inp_mortgageterms.'</CalculationTerm>
				                <IncludeRemainingBalance>false</IncludeRemainingBalance>
				                <UseInitialPeriod>false</UseInitialPeriod>
				            </TotalToPayOptions>
				            <IncludeProducts xmlns="">
				                <EnhancedPanelOnly>false</EnhancedPanelOnly>
				                <Direct>false</Direct>
				                <Exclusives>false</Exclusives>
				                <Stepped>true</Stepped>
				                <Ineligible>false</Ineligible>
				                <Refer>true</Refer>
				                <OnlyVerifiedProducts>false</OnlyVerifiedProducts>
				            </IncludeProducts>
				            <PropertyToBeMortgaged xmlns="">
				                <PropertyValue>'.$inp_propertyvalue.'</PropertyValue>
				                <PropertyType>House</PropertyType>
				                <ResidenceType>Main Residence</ResidenceType>
				                <Location>England</Location>
				                <Tenure>Freehold</Tenure>
				                <ConstructionType>Standard</ConstructionType>
				                <NewBuild>'.(($inp_subdeals == 'NewBuild') ? 'true' : 'false').'</NewBuild>
				            </PropertyToBeMortgaged>
				            <Requirements xmlns="">
				                <FirstTimeBuyer>false</FirstTimeBuyer>
				                <Purpose>'.$inp_purpose.'</Purpose>                
				                <Type>'.$inp_type.'</Type>
				                <RepaymentMethod>'.$inp_repaymentmethod.'</RepaymentMethod>
				                <MortgageTerm>
				                    <Years>'.$inp_mortgageterms.'</Years>
				                    <Months>0</Months>
				                </MortgageTerm>
				                <Incentives>
				                    <ArrangementBookingFees>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </ArrangementBookingFees>
				                    <BorrowBackFacility>false</BorrowBackFacility>
				                    <Cashback>false</Cashback>
				                    <EarlyRepaymentFees>
				                      <NoRepaymentOverhang>false</NoRepaymentOverhang>
				                      <None>'.$inp_noearlyrepayment.'</None>
				                    </EarlyRepaymentFees>
				                    <HigherLendingCharge>
				                      <AbilityToAddToLoan>false</AbilityToAddToLoan>
				                      <None>false</None>
				                    </HigherLendingCharge>
				                    <LegalFees>
				                      <PaidorFree>false</PaidorFree>
				                      <Refunded>false</Refunded>
				                    </LegalFees>
				                    <LinkedCurrentAccount>false</LinkedCurrentAccount>
				                    <NoCompulsoryBuildingInsurance>false</NoCompulsoryBuildingInsurance>
				                    <Overpayments>false</Overpayments>
				                    <PaymentHolidays>false</PaymentHolidays>
				                    <Portable>false</Portable>
				                    <Underpayments>false</Underpayments>
				                    <ValuationFees>
				                      <NoneOrFree>false</NoneOrFree>
				                      <Refunded>false</Refunded>
				                    </ValuationFees>
				                </Incentives>
				                <InitialPeriod>'.$inp_initialperiod.'</InitialPeriod>
				                <InterestRate/>
				                <InterestRateType>
				                    <Fixed>'.$inp_dealsfixed.'</Fixed>
				                    <Variable>'.$inp_dealsvariable.'</Variable>
				                    <Discounted>'.$inp_dealsvariable.'</Discounted>
				                    <Tracker>'.$inp_dealsvariable.'</Tracker>
				                    <Capped>'.$inp_dealsvariable.'</Capped>
				                    <Libor>'.$inp_dealsvariable.'</Libor>
				                    <Offset>'.$inp_offset.'</Offset>
				                </InterestRateType>
				                <Loan>
				                    <Deposit>'.$inp_deposit.'</Deposit>
				                    <LoanAmount>'.$loanamount.'</LoanAmount>
				                    <LTV>'.$ltv.'</LTV>
				                    <HelpToBuy>'.(($inp_subdeals == 'Help') ? 'true' : 'false').'</HelpToBuy>
				                    '.(($inp_subdeals == 'Shared') ? '<GovernmentScheme>Shared Ownership</GovernmentScheme><Share>50</Share><ShareToValue>90</ShareToValue>' : '').'
				                    <BuilderDeposit>false</BuilderDeposit>
				                    <VendorDeposit>false</VendorDeposit>
				                    <CapitalRestOptions>
				                      <Annually>false</Annually>
				                      <Daily>false</Daily>
				                      <Monthly>false</Monthly>
				                      <Quarterley>false</Quarterley>
				                    </CapitalRestOptions>
				                </Loan>
				            </Requirements>
				        </SourcingData>
				    </Data>
				</SourceProductsRequest>');
    	}

    	$xml_post_string = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"><soap12:Body><SourceProducts xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage"><request>'.$string.'</request></SourceProducts></soap12:Body></soap12:Envelope>';

    	$headers = array(
	    "POST /Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx HTTP/1.1",
	    "Host: topaztest.trigoldcrystal.co.uk",
	    "Content-Type: application/soap+xml; charset=utf-8",
	    "Content-Length: ".strlen($xml_post_string)
	    );
	    $url = $soapUrl;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_HEADER, FALSE);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    $response = curl_exec($ch);

	    curl_close($ch);

	    $response1 = str_replace("<soap:Body>","",$response);
	    $response2 = str_replace("</soap:Body>","",$response1);

	    $response_parser = json_encode(simplexml_load_string($response2));
	    $response_array = json_decode($response_parser,true);
	    $filtResponse = simplexml_load_string($response_array['SourceProductsResponse']['SourceProductsResult']);//just parse the result
	    $responseType = (array)$filtResponse->Header->ResponseType;
	    if('Acknowledge' == $responseType[0]){
	    	$correlationID = (array)$filtResponse->Header->CorrelationID;

	    	//now get the 5 products summary
		    $soapUrl = "https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx";
		    $string = htmlentities('<GetMatchedProductSummaryRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/GetMatchedProductSummaryRequest">
		      <Version>3.1</Version>
		      <Header>
		        <CorrelationID xmlns="">'.$correlationID[0].'</CorrelationID>
		        <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
		      </Header>
		      <Data>
		        <PagingCriteria>
		          <SkipCount xmlns="">0</SkipCount>
		          <ReturnCount xmlns="">30</ReturnCount>
		        </PagingCriteria>
		        <SortingCriteria>
		          <SortExpression xmlns="">CalcFields.InitialInterestRate asc</SortExpression>
		        </SortingCriteria>
		      </Data>
		    </GetMatchedProductSummaryRequest>');
		    $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
		    <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
		      <soap12:Body>
		        <GetMatchedProductSummary xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage">
		          <request>'.$string.'</request>
		        </GetMatchedProductSummary>
		      </soap12:Body>
		    </soap12:Envelope>';

		    $headers = array(
		        "POST /Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx HTTP/1.1",
		        "Host: topaztest.trigoldcrystal.co.uk",
		        "Content-Type: application/soap+xml; charset=utf-8",
		        "Content-Length: ".strlen($xml_post_string)
		    );

		    $url = $soapUrl;

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		    curl_setopt($ch, CURLOPT_HEADER, FALSE);
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		    $response = curl_exec($ch);
		    curl_close($ch);

		    $response1 = str_replace("<soap:Body>","",$response);
		    $response2 = str_replace("</soap:Body>","",$response1);

		    $response_parser = json_encode(simplexml_load_string($response2));
		    $response_array = json_decode($response_parser,true);
		    //just parse the result
		    $filtResponse = simplexml_load_string($response_array['GetMatchedProductSummaryResponse']['GetMatchedProductSummaryResult']);
		    $delay = (array)$filtResponse->Header->ResponseEndPoint;
		    if(isset($delay['@attributes']['interval'])){
		        sleep(2);//delay for 2 seconds
		        $soapUrl = "https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx";

		        $string = htmlentities('<GetMatchedProductSummaryRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/GetMatchedProductSummaryRequest">
		          <Version>3.1</Version>
		          <Header>
		            <CorrelationID xmlns="">'.$correlationID[0].'</CorrelationID>
		            <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID>
		          </Header>
		          <Data>
		            <PagingCriteria>
		              <SkipCount xmlns="">0</SkipCount>
		              <ReturnCount xmlns="">30</ReturnCount>
		            </PagingCriteria>
		            <SortingCriteria>
		              <SortExpression xmlns="">CalcFields.InitialInterestRate asc</SortExpression>
		            </SortingCriteria>
		          </Data>
		        </GetMatchedProductSummaryRequest>');

		        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
		        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
		          <soap12:Body>
		            <GetMatchedProductSummary xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage">
		              <request>'.$string.'</request>
		            </GetMatchedProductSummary>
		          </soap12:Body>
		        </soap12:Envelope>';

		        $headers = array(
		            "POST /Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx HTTP/1.1",
		            "Host: topaztest.trigoldcrystal.co.uk",
		            "Content-Type: application/soap+xml; charset=utf-8",
		            "Content-Length: ".strlen($xml_post_string)
		        );

		        $url = $soapUrl;

		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, $url);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		        curl_setopt($ch, CURLOPT_HEADER, FALSE);
		        curl_setopt($ch, CURLOPT_POST, true);
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
		        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		        $response = curl_exec($ch);
		        curl_close($ch);
		    
		        $response1 = str_replace("<soap:Body>","",$response);
		        $response2 = str_replace("</soap:Body>","",$response1);

		        $response_parser = json_encode(simplexml_load_string($response2));
		        $response_array = json_decode($response_parser,true);
		        $filtResponse = simplexml_load_string($response_array['GetMatchedProductSummaryResponse']['GetMatchedProductSummaryResult']);//just parse the result
		        $responseType = (array)$filtResponse->Data->Products;
		        if($responseType){
		            $prodVal = (array)$responseType['Products'];
		            $prodCnt = count($prodVal);
		            for($i=0;$i<$prodCnt;$i++){
		                $productC[] = $prodVal[$i]->CalcFields;
		                $productG[] = $prodVal[$i]->GridViewType;
		                if($i==0){
		                    $mainmonthypayment = $productC[$i]->FollowOnMonthlyPayment;
		                    $mainTotalToPay = $productC[$i]->TotalToPay;
		                }                
		            }
		        }       
		    }else{ //delay if end 
		        $responseType = (array)$filtResponse->Data->Products;
		        if($responseType){
		            $prodVal = (array)$responseType['Products'];
		            $prodCnt = count($prodVal);
		            for($i=0;$i<$prodCnt;$i++){
		                $productC[] = $prodVal[$i]->CalcFields;
		                $productG[] = $prodVal[$i]->GridViewType;
		                if($i==0){
		                    $mainmonthypayment = $productC[$i]->FollowOnMonthlyPayment;
		                    $mainTotalToPay = $productC[$i]->TotalToPay;
		                }         
		            }
		        }
		    }//else end
		    
		    $imgUrl = 'https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Web.UI.Support/App_Themes/images/Providers/Large/';

		    $results ='
		    	<section class="u-clearfix u-section-4 mortgage_values" id="sec-6d42">';

		    if($productC){
				$prevImgLink = [];
				$prevInterestRate = [];
				$sno =0;
				$cntERC = 0;
				$arrangement_fee = '&pound;'.'0.00';
				$booking_fee = '&pound;'.'0.00';
				$valuation_fee = '&pound;'.'0.00';
				$exit_fee = '&pound;'.'0.00';
				$basic_legal = '&pound;'.'0.00';
				$overpayments_allowed =0;
				for($j=0; $j<count($productC); $j++){
            	//Get the Individual Product Detaile by Sending the Product id.
					if($responseType){
		                $soapUrl = "https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx";
		                $string = htmlentities('<GetProductRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage/GetProductRequest">
		                          <Version>3.00</Version> 
		                         <Header>
		                          <CorrelationID xmlns="">'.$correlationID[0].'</CorrelationID> 
		                          <SecurityID xmlns="">'.$_SESSION[$cryptKey].'</SecurityID> 
		                          </Header>
		                         <Data>
		                          <ProductID xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing">'.$productC[$j]->ProductId.'</ProductID> 
		                          <SortingCriteria xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing" /> 
		                          </Data>
		                      </GetProductRequest>');
		                $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
		                <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
		                    <soap12:Body>
		                        <GetProduct xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage">
		                            <request>'.$string.'</request>
		                        </GetProduct>
		                    </soap12:Body>
		                </soap12:Envelope>';
		                $headers = array(
		                    "POST /Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx HTTP/1.1",
		                    "Host: topaztest.trigoldcrystal.co.uk",
		                    "Content-Type: application/soap+xml; charset=utf-8",
		                    "Content-Length: ".strlen($xml_post_string)
		                );       

		                $url = $soapUrl;
		                $ch = curl_init();
		                curl_setopt($ch, CURLOPT_URL, $url);
		                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		                curl_setopt($ch, CURLOPT_HEADER, FALSE);
		                curl_setopt($ch, CURLOPT_POST, true);
		                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
		                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		                $response = curl_exec($ch);  
		                curl_close($ch);
		                $response1 = str_replace("<soap:Body>","",$response);
		                $response2 = str_replace("</soap:Body>","",$response1);

		                $response_parser = json_encode(simplexml_load_string($response2));
		                $response_array = json_decode($response_parser,true);
		                $filtResponseProd = simplexml_load_string($response_array['GetProductResponse']['GetProductResult']);//just parse the result
		                $responseTypeProd = (array)$filtResponseProd->Data->Product; 
		                if($responseTypeProd){
		                    $productMortgageType = (array)$responseTypeProd['ProductMortgageType']; 
		                    $overpayments_allowed = (array)$productMortgageType['Product']->OverPayments->OverpaymentsValue;
		                    $mortgageCalculatedFields = (array)$responseTypeProd['MortgageCalculatedFields'];
		                    $assumedStartDate = $mortgageCalculatedFields['AssumedStartDate'];
		                    //Lender Fees Calculation
		                    $providerFees = (array)$productMortgageType['Product']->ProviderFees;
		                    if($providerFees){
		                        foreach($providerFees as $keyP => $valP){
		                            $lenderFees = 0;
		                            foreach($valP as $keyPFees => $valPFees){
		                                $provFeesDetails = (array)$valPFees;
		                                if($provFeesDetails){
		                                	if(isset($provFeesDetails['Amount']))	
		                                    $lenderFees = $lenderFees + $provFeesDetails['Amount'];
		                                } //provFees if End
		                            }//valP foreach end
		                        }//providerFees foreach End
		                    }//providerFees if end
		                }//responseTypeProd if End
		            }//responseType if end


		            //Early Repayment charge Calculation
		            $ercBands = (array)$productC[$j]->ERCBands;
		            $ercWords ='';
		            if($ercBands){
		                foreach ($ercBands as $keyERC => $valERC) {
		                    $cntERC = 0;
		                    foreach ($valERC as $keyE => $valE) {
		                        $ercCalc = (array)$valE->LoanCalculationBasis;  
		                        if(empty($ercCalc)){
		                            $ercCalc = (array)$valERC->LoanCalculationBasis;  
		                        }
		                        if($ercCalc && $cntERC < 2) {
		                            $ercPeriodBasis = (array)$ercCalc['ERCPeriodBasis'];  
		                            $ercPeriodBasicsChild  = (array)$ercPeriodBasis['PeriodBasis'];
		                            if(empty($EndDate) && count($ercPeriodBasicsChild)){
		                                $ercUpperUnits = $ercPeriodBasicsChild['UpperPeriodUnits'];
		                                $ercPeriodType = $ercPeriodBasicsChild['PeriodType'];
		                                $ercEndDate = date('d-M-Y', strtotime("+".$ercUpperUnits." ".$ercPeriodType, strtotime($assumedStartDate)));
		                            }else{
		                            	if(isset($ercPeriodBasis['EndDate']))
		                                $ercEndDate = date('d-M-Y', strtotime($ercPeriodBasis['EndDate']));
		                            }
		                            if(isset($ercCalc['PercentageOfLoan']))
		                            $ercPercentage = $ercCalc['PercentageOfLoan'];
		                            $ercWords .= $ercPercentage .'% of loan amount redeemed to be paid until '.$ercEndDate .'; ';
		                            $cntERC++;
		                        }		                    
		                    }//foreach end
		                }//ercBand if end
		            }else{
		                if($productC[$j]->ERC == 'No'){
		                    $ercWords = "No early repayment charge";
		                }
		            }

		            $imageLink = $imgUrl.$productG[$j]->Product->ImageCode;
		            //Arrangement Fee, Booking Fee, Valuation Fee, Basic Legal, Exit Fee, Lender Fee
		            $displayFees = (array)$productC[$j]->DisplayFees;
		            if($displayFees){
		            	foreach($displayFees as $keyF => $valF){
		            		$arrangement_fee = '&pound;'.'0.00';
					        $booking_fee = '&pound;'.'0.00';
					        $valuation_fee = '&pound;'.'0.00';
					        $exit_fee = '&pound;'.'0.00';
					        $basic_legal = '&pound;'.'0.00';
		            		foreach($valF as $keyFees => $valFees){
			            		$feesDetails = (array)$valFees;
			            		if (strpos($feesDetails['Description'], 'Arrangement Fee') !== false) {                	 
				                    $arrangement_fee = '&pound;'.$feesDetails['Amount'];
				                }
				                if (strpos($feesDetails['Description'], 'Booking Fee') !== false) {
				                    $booking_fee = '&pound;'.$feesDetails['Amount'];
				                }
				                if (strpos($feesDetails['Description'], 'Valuation Fee') !== false) {
				                    $valuation_fee = '&pound;'.$feesDetails['Amount'];
				                }
				                if (strpos($feesDetails['Description'], 'Exit Fee') || strpos(isset($feesDetails['FeeDescription']), 'Exit Fee') !== false) {
				                    $exit_fee = '&pound;'.$feesDetails['Amount'];
				                }
				                if (strpos($feesDetails['Description'], 'Lender Conveyancing Fee') || strpos(isset($feesDetails['FeeDescription']), 'Lenders Conveyancing Fee')  || strpos($feesDetails['Type'], 'Lender Conveyancing Fee') !== false) {
				                    $basic_legal = '&pound;'.$feesDetails['Amount'];
				                }
		            		}//foreach end            		
		            	}//foreach displayFees end            
		            }//displayFees if end

		            if($sno <=10){
		                if($j == 0){
		                	$results .= '
			            	<div class="u-clearfix u-sheet u-sheet-1" id='.$productC[$j]->ProductId.'>
			            	<div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-1">
				                <div class="u-container-layout u-container-layout-1">
				                    <h3 class="u-text u-text-1">Type</h3>
				                    <p class="u-text u-text-2">'.$productC[$j]->InitialInterestType.'</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-2">
				                <div class="u-container-layout u-container-layout-2">
				                    <h3 class="u-text u-text-3">Initial Rate</h3>
				                    <p class="u-text u-text-4">'.$productC[$j]->InitialInterestRate.'%</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-3">
				                <div class="u-container-layout u-container-layout-3">
				                    <h3 class="u-text u-text-5">Initial Payment</h3>
				                    <p class="u-text u-text-6">&pound;'.$productC[$j]->InitialMonthlyPayment.'</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-4">
				                <div class="u-container-layout u-container-layout-4">
				                    <h3 class="u-text u-text-7">Type, Period & LTV</h3>
				                    <p class="u-text u-text-8">'.$productC[$j]->InitialInterestType.' Until '.$productC[$j]->InitialInterestPeriod.' Upto '.$productC[$j]->MaxLTV. '% LTV</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-5">
				                <div class="u-container-layout u-container-layout-5">
				                    <h3 class="u-text u-text-9">Display Fees</h3>
				                    <p class="u-text u-text-10">Arrangement Fee - '.$arrangement_fee.'<br>Booking Fee - '.$booking_fee.'<br>Valuation Fee - '.$valuation_fee.'</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-6">
				                <div class="u-container-layout u-container-layout-6">
				                    <h3 class="u-text u-text-11">Other Fees</h3>
				                    <p class="u-text u-text-12">Exit Fee - '.$exit_fee.'<br>Basic Legals - '.$basic_legal .'</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-7">
				                <div class="u-container-layout u-container-layout-7">
				                    <h3 class="u-text u-text-13">Other Information</h3>
				                    <p class="u-text u-text-14">Lender Fees - &pound;'. $lenderFees.'<br>Reverting to - '.$productC[$j]->FollowOnInterestRate.'%<br>Overall cost for comparison - '.$productC[$j]->APR.'%<br>OverPayments Allowed - '.((isset($overpayments_allowed['MaxPercentage'])!='')? $overpayments_allowed['MaxPercentage']:0) .'%<br>Early Repayment Charge -'.$ercWords.'</p>
				                </div>
				            </div>
				            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-8">
				                <div class="u-container-layout u-container-layout-8">
				                    <h2 class="u-text u-text-15">Provider</h2>
				                    <div class="u-border-3 u-border-grey-dark-1 u-line u-line-horizontal u-line-1"></div>
				                    	<span class="u-icon u-icon-circle u-text-palette-1-base u-icon-1">
				                    	<img src="'. $imageLink .'_Large.gif">
				                    	<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="svg-69b2" x="0px" y="0px" viewBox="0 0 58 58" style="enable-background:new 0 0 58 58;" xml:space="preserve" class="u-svg-content">
				                <g>
				                    <path d="M57,6H1C0.448,6,0,6.447,0,7v44c0,0.553,0.448,1,1,1h56c0.552,0,1-0.447,1-1V7C58,6.447,57.552,6,57,6z M56,50H2V8h54V50z"></path>
				                    <path d="M16,28.138c3.071,0,5.569-2.498,5.569-5.568C21.569,19.498,19.071,17,16,17s-5.569,2.498-5.569,5.569   C10.431,25.64,12.929,28.138,16,28.138z M16,19c1.968,0,3.569,1.602,3.569,3.569S17.968,26.138,16,26.138s-3.569-1.601-3.569-3.568   S14.032,19,16,19z"></path>
				                    <path d="M7,46c0.234,0,0.47-0.082,0.66-0.249l16.313-14.362l10.302,10.301c0.391,0.391,1.023,0.391,1.414,0s0.391-1.023,0-1.414   l-4.807-4.807l9.181-10.054l11.261,10.323c0.407,0.373,1.04,0.345,1.413-0.062c0.373-0.407,0.346-1.04-0.062-1.413l-12-11   c-0.196-0.179-0.457-0.268-0.72-0.262c-0.265,0.012-0.515,0.129-0.694,0.325l-9.794,10.727l-4.743-4.743   c-0.374-0.373-0.972-0.392-1.368-0.044L6.339,44.249c-0.415,0.365-0.455,0.997-0.09,1.412C6.447,45.886,6.723,46,7,46z"></path>
				                </g>
				                <g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
				                </svg></span></div>
				            </div>
				            <div class="u-container-style u-group u-palette-5-base u-group-9">
				                <div class="u-container-layout u-container-layout-9">
				                    <h3 class="u-text u-text-16">Representative Example</h3>
				                    <p class="u-text u-text-17"> For example, on a mortgage of &pound;'.$inp_mortgageamount.' over a term of '.$inp_mortgageterms.' years on an interest rate of '.$productC[$j]->InitialInterestRate.'%, the initial monthly payment would be &pound;'.$productC[$j]->InitialMonthlyPayment.' the total mortgage application fees would be &pound;'.$productC[$j]->TotalDisplayFees. ', the total cost of the loan would be &pound;'.$productC[$j]->TotalToPayOverMortgageTerm.' and the APRC would be '.$productC[$j]->APR.'%. 
				                    </p>
				                </div>
				            </div>
				            </div>';
		                   
		                    $sno++;
		                }
		                else{
		                    if(($productC[$j]->InitialInterestRate <> $prevInterestRate[$j][0])  || ($imageLink <> $prevImgLink[$j])){

		                    	$results .= '
				            	<div class="u-clearfix u-sheet u-sheet-1" id='.$productC[$j]->ProductId.'>
				            	<div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-1">
					                <div class="u-container-layout u-container-layout-1">
					                    <h3 class="u-text u-text-1">Type</h3>
					                    <p class="u-text u-text-2">'.$productC[$j]->InitialInterestType.'</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-2">
					                <div class="u-container-layout u-container-layout-2">
					                    <h3 class="u-text u-text-3">Initial Rate</h3>
					                    <p class="u-text u-text-4">'.$productC[$j]->InitialInterestRate.'%</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-3">
					                <div class="u-container-layout u-container-layout-3">
					                    <h3 class="u-text u-text-5">Initial Payment</h3>
					                    <p class="u-text u-text-6">&pound;'.$productC[$j]->InitialMonthlyPayment.'</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-4">
					                <div class="u-container-layout u-container-layout-4">
					                    <h3 class="u-text u-text-7">Type, Period & LTV</h3>
					                    <p class="u-text u-text-8">'.$productC[$j]->InitialInterestType.' Until '.$productC[$j]->InitialInterestPeriod.' Upto '.$productC[$j]->MaxLTV. '% LTV</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-5">
					                <div class="u-container-layout u-container-layout-5">
					                    <h3 class="u-text u-text-9">Display Fees</h3>
					                    <p class="u-text u-text-10">Arrangement Fee - '.$arrangement_fee.'<br>Booking Fee - '.$booking_fee.'<br>Valuation Fee - '.$valuation_fee.'</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-6">
					                <div class="u-container-layout u-container-layout-6">
					                    <h3 class="u-text u-text-11">Other Fees</h3>
					                    <p class="u-text u-text-12">Exit Fee - '.$exit_fee.'<br>Basic Legals - '.$basic_legal .'</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-7">
					                <div class="u-container-layout u-container-layout-7">
					                    <h3 class="u-text u-text-13">Other Information</h3>
					                    <p class="u-text u-text-14">Lender Fees - &pound;'. $lenderFees.'<br>Reverting to - '.$productC[$j]->FollowOnInterestRate.'%<br>Overall cost for comparison - '.$productC[$j]->APR.'%<br>OverPayments Allowed - '.((isset($overpayments_allowed['MaxPercentage'])!='')? $overpayments_allowed['MaxPercentage']:0) .'%<br>Early Repayment Charge -'.$ercWords.'</p>
					                </div>
					            </div>
					            <div class="u-border-1 u-border-grey-dark-1 u-container-style u-group u-group-8">
					                <div class="u-container-layout u-container-layout-8">
					                    <h2 class="u-text u-text-15">Provider</h2>
					                    <div class="u-border-3 u-border-grey-dark-1 u-line u-line-horizontal u-line-1"></div>
					                    	<span class="u-icon u-icon-circle u-text-palette-1-base u-icon-1">
					                    	<img src="'. $imageLink .'_Large.gif">
					                    	<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="svg-69b2" x="0px" y="0px" viewBox="0 0 58 58" style="enable-background:new 0 0 58 58;" xml:space="preserve" class="u-svg-content">
					                <g>
					                    <path d="M57,6H1C0.448,6,0,6.447,0,7v44c0,0.553,0.448,1,1,1h56c0.552,0,1-0.447,1-1V7C58,6.447,57.552,6,57,6z M56,50H2V8h54V50z"></path>
					                    <path d="M16,28.138c3.071,0,5.569-2.498,5.569-5.568C21.569,19.498,19.071,17,16,17s-5.569,2.498-5.569,5.569   C10.431,25.64,12.929,28.138,16,28.138z M16,19c1.968,0,3.569,1.602,3.569,3.569S17.968,26.138,16,26.138s-3.569-1.601-3.569-3.568   S14.032,19,16,19z"></path>
					                    <path d="M7,46c0.234,0,0.47-0.082,0.66-0.249l16.313-14.362l10.302,10.301c0.391,0.391,1.023,0.391,1.414,0s0.391-1.023,0-1.414   l-4.807-4.807l9.181-10.054l11.261,10.323c0.407,0.373,1.04,0.345,1.413-0.062c0.373-0.407,0.346-1.04-0.062-1.413l-12-11   c-0.196-0.179-0.457-0.268-0.72-0.262c-0.265,0.012-0.515,0.129-0.694,0.325l-9.794,10.727l-4.743-4.743   c-0.374-0.373-0.972-0.392-1.368-0.044L6.339,44.249c-0.415,0.365-0.455,0.997-0.09,1.412C6.447,45.886,6.723,46,7,46z"></path>
					                </g>
					                <g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
					                </svg></span></div>
					            </div>
					            <div class="u-container-style u-group u-palette-5-base u-group-9">
					                <div class="u-container-layout u-container-layout-9">
					                    <h3 class="u-text u-text-16">Representative Example</h3>
					                    <p class="u-text u-text-17"> For example, on a mortgage of &pound;'.$inp_mortgageamount.' over a term of '.$inp_mortgageterms.' years on an interest rate of '.$productC[$j]->InitialInterestRate.'%, the initial monthly payment would be &pound;'.$productC[$j]->InitialMonthlyPayment.' the total mortgage application fees would be &pound;'.$productC[$j]->TotalDisplayFees. ', the total cost of the loan would be &pound;'.$productC[$j]->TotalToPayOverMortgageTerm.' and the APRC would be '.$productC[$j]->APR.'%. 
					                    </p>
					                </div>
					            </div>
					            </div>';		                       
		                        $sno++;                    
		                    }//if end		                                
		                }//else end
		                $prevImgLink[$j+1] = $imageLink;
		                $prevInterestRate[$j+1] = (array)$productC[$j]->InitialInterestRate;
		            }//if end
				}//for end
		    }//productC if end

		    $results .= '</section>';

		    echo $results;
		    exit;
	    } //Acknowledge if end
	    else{
		    $err_desc = (array)$filtResponse->Errors->Error->Description;
		    echo '<div id="reusltdiv">please try after sometime_1; '.$err_desc[0].'</div>';//we have to log this 'GetTokenRequest ERROR -> '.$err_desc[0];
		    exit;
		}//Acknowledge else end
	}// if end for session check
}//if end for isset of POST.


//Function to create the Token to access the source 
function loginAPI($cryptKey, $cryptKeyexpire){

	$soapUrl = 'https://topaztest.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx';
    $installationKey = '{3c6116a0-96c0-4ee0-b353-06b6b99545bf}';
    $username        = 'MortgageScoutTest';//Test Credentials
    $password        = '$4EJCckt';
    $string = htmlentities('<GetTokenRequest xmlns="http://www.TrigoldCrystal.co.uk/Services/GetTokenRequest">
      <Version>1.00</Version>
      <Header>
        <CorrelationID xmlns=""></CorrelationID>
        <SecurityID xmlns=""></SecurityID>
        <TransactionID xmlns=""></TransactionID>
      </Header>
      <Data>
        <InstallationID>'.$installationKey.'</InstallationID>
        <UserName>'.$username.'</UserName>
        <Password>'.$password.'</Password>
      </Data>
    </GetTokenRequest>');//string which need to pass as xmlMessage is ready
    //SOAP 1.2
    //get the relevant format and headers from here : https://topaz.trigoldcrystal.co.uk/Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx?op=GetToken
    $xml_post_string = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"><soap12:Body><GetToken xmlns="http://www.TrigoldCrystal.co.uk/Services/Sourcing/Mortgage"><xmlMessage>'.$string.'</xmlMessage></GetToken></soap12:Body></soap12:Envelope>';
    $headers = array(
        "POST /Crystal.Momentum.Services.Sourcing.Service/MortgageSourcing.asmx HTTP/1.1",
        "Host: topaztest.trigoldcrystal.co.uk",
        "Content-Type: application/soap+xml; charset=utf-8",
        "Content-Length: ".strlen($xml_post_string)
    );
    //so lets use curl to load send the request and receive response
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $soapUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);//reponse will be here
    if(curl_errno($ch)){
        echo '<div id="reusltdiv">please try after sometime_2; '.curl_errno($ch).'</div>';//we have to log this 'CURL ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
        exit;
    }
    else{
        $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if(404 == $returnCode){
            echo '<div id="reusltdiv">please try after sometime_3;'.curl_errno($ch).'</div>';//we have to log this 'CURL ERROR -> 404 Not Found';
            exit;
        }
    }
    curl_close($ch);
    //lets parse the response XML format
    $response1 = str_replace("<soap:Body>","",$response);
    $response2 = str_replace("</soap:Body>","",$response1);
    $response_parser = json_encode(simplexml_load_string($response2));
    $response_array = json_decode($response_parser,true);
    $filtResponse = simplexml_load_string($response_array['GetTokenResponse']['GetTokenResult']);//just parse the result
    $responseType = (array)$filtResponse->Header->ResponseType;
    if('Response' == $responseType[0]){//Acknowledge, Response, Errors; we are not using acknowledge here.
       //set the reponse token into session it can live for 2 hours at trigold end, so lets k0ep as 100mins
       $responseToken = (array)$filtResponse->Data->Response->Token;
       //$responseStatus = (array)$filtResponse->Data->AccessStatus;
       //we have to log this 'GetTokenResponse -> '.$responseStatus[0];
       //now set seesion and expire duration
       $_SESSION[$cryptKeyexpire] = time() + (100 * 60);//100 mins
       $_SESSION[$cryptKey] = $responseToken[0];
       header("Refresh:0");//reload the page just doing for localhost
    }else{
       $err_desc = (array)$filtResponse->Errors->Error->Description;
       echo '<div id="reusltdiv">please try after sometime_4; '.curl_errno($ch).'</div>';//we have to log this 'GetTokenRequest ERROR -> '.$err_desc[0];
       exit;
    }
}
