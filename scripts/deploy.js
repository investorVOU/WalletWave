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
  const contractData = {
    address: cryptoFund.address,
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