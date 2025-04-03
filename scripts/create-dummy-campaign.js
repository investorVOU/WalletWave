
const { ethers } = require("hardhat");

async function main() {
  const contractAddress = "0x5FbDB2315678afecb367f032d93F642f64180aa3";
  const CryptoFund = await ethers.getContractFactory("CryptoFund");
  const contract = CryptoFund.attach(contractAddress);

  // Dummy campaign data
  const title = "Test DApp Development";
  const description = "A test campaign for DApp development and testing";
  const goal = ethers.utils.parseEther("0.1"); // 0.1 ETH
  const durationInDays = 30;

  console.log("Creating dummy campaign...");
  const tx = await contract.createCampaign(title, description, goal, durationInDays);
  const receipt = await tx.wait();

  const event = receipt.events.find(e => e.event === 'CampaignCreated');
  console.log(`Campaign created with ID: ${event.args.id}`);
  console.log(`Transaction hash: ${receipt.transactionHash}`);
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
