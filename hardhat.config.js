require("@nomiclabs/hardhat-waffle");
require("@nomiclabs/hardhat-ethers");

/**
 * @type import('hardhat/config').HardhatUserConfig
 */
module.exports = {
  solidity: "0.8.4",
  networks: {
    hardhat: {
      chainId: 31337,
    },
    sepolia: {
      url: "https://sepolia.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1",
      accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : [],
    },
    goerli: {
      url: "https://goerli.infura.io/v3/27e484dcd9e3efcfd25a83a78777cdf1",
      accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : [],
    },
    mumbai: {
      url: "https://rpc-mumbai.maticvigil.com",
      accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : [],
    }
  },
  paths: {
    artifacts: "./artifacts",
    cache: "./cache",
    sources: "./contracts",
    tests: "./test"
  }
};