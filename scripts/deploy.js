const { ethers } = require("hardhat");

async function main() {
  console.log("Deploying CryptoFund contract...");
  
  // Get the contract factory
  const CryptoFund = await ethers.getContractFactory("CryptoFund");
  
  // Deploy the contract
  const cryptoFund = await CryptoFund.deploy();
  
  // Wait for deployment to finish
  await cryptoFund.deployed();
  
  console.log("CryptoFund deployed to:", cryptoFund.address);
  
  // Save the contract address and ABI to a JSON file
  const fs = require("fs");
  // Get network information
  const network = await ethers.provider.getNetwork();
  let networkName = "Unknown Network";
  
  switch (network.chainId) {
    case 1:
      networkName = "Ethereum Mainnet";
      break;
    case 11155111:
      networkName = "Sepolia Testnet";
      break;
    case 5:
      networkName = "Goerli Testnet";
      break;
    case 137:
      networkName = "Polygon Mainnet";
      break;
    case 80001:
      networkName = "Mumbai Testnet";
      break;
    case 31337:
      networkName = "Hardhat Network";
      break;
    default:
      networkName = `Chain ID ${network.chainId}`;
  }
  
  console.log(`Deployed on: ${networkName} (Chain ID: ${network.chainId})`);
  
  const contractData = {
    address: cryptoFund.address,
    networkId: network.chainId,
    networkName: networkName,
    abi: JSON.parse(cryptoFund.interface.format('json'))
  };
  
  fs.writeFileSync('contract-data.json', JSON.stringify(contractData, null, 2));
  console.log("Contract data saved to contract-data.json");
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });