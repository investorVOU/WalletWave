// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

/**
 * @title CryptoFund
 * @dev A crowdfunding contract with staking capabilities
 */
contract CryptoFund {
    struct Campaign {
        uint256 id;
        address payable owner;
        string title;
        string description;
        uint256 goal;
        uint256 currentAmount;
        uint256 endTime;
        bool claimed;
        bool canceled;
    }
    
    struct StakingContribution {
        uint256 amount;
        uint256 stakingPeriod;  // in seconds
        uint256 stakingEndTime;
        uint256 reward;
        bool claimed;
    }
    
    mapping(uint256 => Campaign) public campaigns;
    mapping(uint256 => mapping(address => uint256)) public contributions;
    mapping(uint256 => mapping(address => StakingContribution)) public stakingContributions;
    
    uint256 public campaignCount = 0;
    uint256 public stakingRewardRate = 5; // 5% APY
    
    event CampaignCreated(uint256 indexed id, address indexed owner, string title, uint256 goal, uint256 endTime);
    event ContributionMade(uint256 indexed campaignId, address indexed contributor, uint256 amount);
    event StakingContributionMade(uint256 indexed campaignId, address indexed contributor, uint256 amount, uint256 stakingPeriod);
    event CampaignFundsClaimed(uint256 indexed campaignId, address indexed owner, uint256 amount);
    event StakingRewardClaimed(uint256 indexed campaignId, address indexed contributor, uint256 reward);
    event CampaignCanceled(uint256 indexed campaignId);
    event ContributionRefunded(uint256 indexed campaignId, address indexed contributor, uint256 amount);
    
    /**
     * @dev Create a new campaign
     */
    function createCampaign(
        string memory _title,
        string memory _description,
        uint256 _goal,
        uint256 _durationInDays
    ) external returns (uint256) {
        require(_goal > 0, "Goal must be greater than 0");
        require(_durationInDays > 0, "Duration must be greater than 0");
        
        campaignCount++;
        uint256 endTime = block.timestamp + (_durationInDays * 1 days);
        
        campaigns[campaignCount] = Campaign({
            id: campaignCount,
            owner: payable(msg.sender),
            title: _title,
            description: _description,
            goal: _goal,
            currentAmount: 0,
            endTime: endTime,
            claimed: false,
            canceled: false
        });
        
        emit CampaignCreated(campaignCount, msg.sender, _title, _goal, endTime);
        return campaignCount;
    }
    
    /**
     * @dev Contribute to a campaign (regular contribution)
     */
    function contribute(uint256 _campaignId) external payable {
        Campaign storage campaign = campaigns[_campaignId];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(block.timestamp < campaign.endTime, "Campaign has ended");
        require(!campaign.canceled, "Campaign has been canceled");
        require(msg.value > 0, "Contribution must be greater than 0");
        
        campaign.currentAmount += msg.value;
        contributions[_campaignId][msg.sender] += msg.value;
        
        emit ContributionMade(_campaignId, msg.sender, msg.value);
    }
    
    /**
     * @dev Contribute to a campaign with staking
     */
    function contributeWithStaking(uint256 _campaignId, uint256 _stakingPeriodInDays) external payable {
        Campaign storage campaign = campaigns[_campaignId];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(block.timestamp < campaign.endTime, "Campaign has ended");
        require(!campaign.canceled, "Campaign has been canceled");
        require(msg.value > 0, "Contribution must be greater than 0");
        require(_stakingPeriodInDays >= 30, "Staking period must be at least 30 days");
        require(_stakingPeriodInDays <= 365, "Staking period cannot exceed 365 days");
        
        uint256 stakingPeriod = _stakingPeriodInDays * 1 days;
        uint256 stakingEndTime = block.timestamp + stakingPeriod;
        
        // Calculate staking reward based on APY and staking period
        uint256 reward = calculateStakingReward(msg.value, stakingPeriod);
        
        campaign.currentAmount += msg.value;
        
        // Record staking contribution
        stakingContributions[_campaignId][msg.sender] = StakingContribution({
            amount: msg.value,
            stakingPeriod: stakingPeriod,
            stakingEndTime: stakingEndTime,
            reward: reward,
            claimed: false
        });
        
        emit StakingContributionMade(_campaignId, msg.sender, msg.value, stakingPeriod);
    }
    
    /**
     * @dev Calculate staking reward based on APY and staking period
     */
    function calculateStakingReward(uint256 _amount, uint256 _stakingPeriod) internal view returns (uint256) {
        // APY calculation: amount * APY * (stakingPeriod / 365 days)
        return (_amount * stakingRewardRate * _stakingPeriod) / (100 * 365 days);
    }
    
    /**
     * @dev Claim campaign funds by the owner
     */
    function claimCampaignFunds(uint256 _campaignId) external {
        Campaign storage campaign = campaigns[_campaignId];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(msg.sender == campaign.owner, "Only campaign owner can claim funds");
        require(block.timestamp >= campaign.endTime, "Campaign has not ended yet");
        require(campaign.currentAmount >= campaign.goal, "Campaign goal not reached");
        require(!campaign.claimed, "Funds already claimed");
        require(!campaign.canceled, "Campaign has been canceled");
        
        campaign.claimed = true;
        
        uint256 amountToTransfer = campaign.currentAmount;
        campaign.owner.transfer(amountToTransfer);
        
        emit CampaignFundsClaimed(_campaignId, campaign.owner, amountToTransfer);
    }
    
    /**
     * @dev Claim staking rewards after staking period
     */
    function claimStakingReward(uint256 _campaignId) external {
        Campaign storage campaign = campaigns[_campaignId];
        StakingContribution storage staking = stakingContributions[_campaignId][msg.sender];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(staking.amount > 0, "No staking contribution found");
        require(block.timestamp >= staking.stakingEndTime, "Staking period has not ended yet");
        require(!staking.claimed, "Reward already claimed");
        
        staking.claimed = true;
        
        uint256 totalAmount = staking.amount + staking.reward;
        payable(msg.sender).transfer(totalAmount);
        
        emit StakingRewardClaimed(_campaignId, msg.sender, staking.reward);
    }
    
    /**
     * @dev Cancel a campaign (only owner, before end time)
     */
    function cancelCampaign(uint256 _campaignId) external {
        Campaign storage campaign = campaigns[_campaignId];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(msg.sender == campaign.owner, "Only campaign owner can cancel");
        require(block.timestamp < campaign.endTime, "Campaign has already ended");
        require(!campaign.claimed, "Funds already claimed");
        require(!campaign.canceled, "Campaign already canceled");
        
        campaign.canceled = true;
        
        emit CampaignCanceled(_campaignId);
    }
    
    /**
     * @dev Refund contribution if campaign is canceled or goal not reached
     */
    function refundContribution(uint256 _campaignId) external {
        Campaign storage campaign = campaigns[_campaignId];
        uint256 contribution = contributions[_campaignId][msg.sender];
        
        require(campaign.id > 0, "Campaign does not exist");
        require(contribution > 0, "No contribution found");
        require(
            campaign.canceled || 
            (block.timestamp >= campaign.endTime && campaign.currentAmount < campaign.goal), 
            "Refund conditions not met"
        );
        
        contributions[_campaignId][msg.sender] = 0;
        
        payable(msg.sender).transfer(contribution);
        
        emit ContributionRefunded(_campaignId, msg.sender, contribution);
    }
    
    /**
     * @dev Get details of a campaign
     */
    function getCampaign(uint256 _campaignId) external view returns (
        uint256 id,
        address owner,
        string memory title,
        string memory description,
        uint256 goal,
        uint256 currentAmount,
        uint256 endTime,
        bool claimed,
        bool canceled
    ) {
        Campaign memory campaign = campaigns[_campaignId];
        require(campaign.id > 0, "Campaign does not exist");
        
        return (
            campaign.id,
            campaign.owner,
            campaign.title,
            campaign.description,
            campaign.goal,
            campaign.currentAmount,
            campaign.endTime,
            campaign.claimed,
            campaign.canceled
        );
    }
    
    /**
     * @dev Get staking contribution details of a user
     */
    function getStakingContribution(uint256 _campaignId, address _contributor) external view returns (
        uint256 amount,
        uint256 stakingPeriod,
        uint256 stakingEndTime,
        uint256 reward,
        bool claimed
    ) {
        StakingContribution memory staking = stakingContributions[_campaignId][_contributor];
        
        return (
            staking.amount,
            staking.stakingPeriod,
            staking.stakingEndTime,
            staking.reward,
            staking.claimed
        );
    }
}