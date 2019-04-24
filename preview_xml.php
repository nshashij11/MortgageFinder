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
	$inp_subdeals = $_POST['sub_deals'];

	$inp_purpose = ('Purchase' == $_POST['purpose']) ? 'Purchase' : 'Remortgage';
	$inp_type = ('Residential' == $_POST['type']) ? 'Residential' : 'buy-to-let';

	$inp_basicsalary = 38000;
	$inp_deposit = $inp_propertyvalue - $inp_mortgageamount;
	$loanamount = $inp_mortgageamount;
    if($loanamount <= 0){
        $loanamount = 0;
    }
    $ltv = round(($loanamount / $inp_propertyvalue) * 100);


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
    	}else if('buy-to-let' == $inp_mortgagetype){
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

    	echo "<pre>".$xml_post_string."</pre>";
    	exit;	   
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
