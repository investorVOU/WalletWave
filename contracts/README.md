# CryptoFund Smart Contract

This is a crowdfunding smart contract with staking capabilities. It allows users to create campaigns, contribute to campaigns, and stake their contributions for rewards.

## Features

- Create campaigns with a title, description, funding goal, and duration
- Regular contributions to campaigns
- Staking contributions with rewards based on APY and staking period
- Campaign owners can claim funds after successful campaign completion
- Contributors can claim staking rewards after staking period
- Refund system for failed or canceled campaigns

## Contract Details

- **Contract Name:** CryptoFund
- **Solidity Version:** ^0.8.0
- **License:** MIT

## Deployment Instructions

### Prerequisites

- Node.js and npm installed
- Hardhat installed
- Wallet with testnet ETH (for testnet deployment)
- Infura.io account (for testnet deployment)

### Local Development

1. Compile the contract:
   ```
   npx hardhat compile
   ```

2. Run a local Hardhat node:
   ```
   npx hardhat node
   ```

3. Deploy to local network:
   ```
   npx hardhat run scripts/deploy.js --network localhost
   ```

### Testnet Deployment

1. Update `hardhat.config.js` with your Infura API key and wallet mnemonic

2. Deploy to Goerli testnet:
   ```
   npx hardhat run scripts/deploy.js --network goerli
   ```

3. Deploy to Mumbai Polygon testnet:
   ```
   npx hardhat run scripts/deploy.js --network mumbai
   ```

## Contract Functions

### Campaign Management

- `createCampaign(string _title, string _description, uint256 _goal, uint256 _durationInDays)`: Create a new campaign
- `cancelCampaign(uint256 _campaignId)`: Cancel a campaign (only owner)
- `claimCampaignFunds(uint256 _campaignId)`: Claim funds after successful campaign (only owner)
- `getCampaign(uint256 _campaignId)`: Get campaign details

### Contribution Management

- `contribute(uint256 _campaignId)`: Make a regular contribution
- `contributeWithStaking(uint256 _campaignId, uint256 _stakingPeriodInDays)`: Make a staking contribution
- `claimStakingReward(uint256 _campaignId)`: Claim staking rewards after staking period
- `refundContribution(uint256 _campaignId)`: Request a refund if campaign is canceled or goal not reached
- `getStakingContribution(uint256 _campaignId, address _contributor)`: Get staking contribution details

## Integration with Web Application

After deploying the contract, update the web application to interact with it:

1. Save the contract address and ABI from the deployment
2. Update the `js/app.js` file to use the contract address and ABI
3. Implement the necessary functions to interact with the contract

## Security Considerations

- The contract uses simple reward calculations and should be audited before production use
- Always thoroughly test on testnets before mainnet deployment
- Gas optimization could be improved for large-scale usage