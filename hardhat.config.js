require("@nomiclabs/hardhat-waffle");
require("@nomiclabs/hardhat-ethers");

/**
 * @type import('hardhat/config').HardhatUserConfig
 */
module.exports = {
  solidity: "0.8.4",
  networks: {
    hardhat: {
      chainId: 1337
    },
    goerli: {
      url: "https://goerli.infura.io/v3/YOUR_INFURA_KEY",
      accounts: { mnemonic: "YOUR_MNEMONIC" },
    },
    mumbai: {
      url: "https://polygon-mumbai.infura.io/v3/YOUR_INFURA_KEY",
      accounts: { mnemonic: "YOUR_MNEMONIC" },
    }
  },
  paths: {
    sources: "./contracts",
    artifacts: "./artifacts",
  },
  // Disable telemetry
  analytics: {
    enabled: false
  }
};